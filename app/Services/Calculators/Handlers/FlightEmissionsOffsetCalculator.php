<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Flight Emissions + Offset Calculator
 * ICAO-style CO₂e, radiative-forcing sensitivity, cabin penalty, offset cost.
 */
class FlightEmissionsOffsetCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'flight_emissions_offset_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance_km', 'One-Way Route Distance', 'number', ['min' => 50, 'max' => 20000, 'step' => 10, 'default' => 5500, 'unit' => 'km']),
            $this->field('route_label', 'Route Label (optional)', 'string', ['default' => 'e.g. JFK–LHR', 'required' => false]),
            $this->field('cabin_class', 'Cabin Class', 'select', [
                'options' => [
                    'economy' => 'Economy',
                    'premium_economy' => 'Premium economy',
                    'business' => 'Business',
                    'first' => 'First',
                ],
                'default' => 'economy',
            ]),
            $this->field('round_trips_per_year', 'Round-Trips / Year', 'number', ['min' => 1, 'max' => 50, 'step' => 1, 'default' => 2]),
            $this->field('offset_tier', 'Offset Price Tier', 'select', [
                'options' => [
                    'budget' => 'Budget (~$12/t)',
                    'mid' => 'Verified mid (~$25/t)',
                    'premium' => 'High-quality (~$45/t Atmosfair/Gold-like)',
                ],
                'default' => 'mid',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $dist = $this->requireNumeric($inputs, 'distance_km');
        $label = $this->toString($inputs, 'route_label', '');
        $cabin = $this->toString($inputs, 'cabin_class', 'economy');
        $trips = max(1, (int) round($this->requireNumeric($inputs, 'round_trips_per_year')));
        $tier = $this->toString($inputs, 'offset_tier', 'mid');

        // ICAO-ish: kg CO2 per passenger-km (economy baseline), short vs long haul
        $kgPerKm = $dist < 1500 ? 0.155 : ($dist < 4000 ? 0.12 : 0.105);
        $cabinMult = match ($cabin) {
            'premium_economy' => 1.6,
            'business' => 2.9,
            'first' => 4.0,
            default => 1.0,
        };
        $rfMult = 1.9; // Lee et al. 2021-style non-CO2 / RF inclusive factor (illustrative)

        $oneWayKg = $dist * $kgPerKm * $cabinMult;
        $annualKg = $oneWayKg * 2 * $trips;
        $annualT = $annualKg / 1000;
        $annualTRf = $annualT * $rfMult;

        $economyAnnualT = ($dist * $kgPerKm * 1.0 * 2 * $trips) / 1000;
        $cabinPenalty = $annualT - $economyAnnualT;

        $price = match ($tier) {
            'budget' => 12.0,
            'premium' => 45.0,
            default => 25.0,
        };
        $offsetCost = $annualT * $price;
        $offsetCostRf = $annualTRf * $price;

        return [
            'results' => [
                'annual_co2e_tonnes' => $this->round($annualT, 2),
                'annual_co2e_with_radiative_forcing_t' => $this->round($annualTRf, 2),
                'cabin_penalty_vs_economy_t' => $this->round($cabinPenalty, 2),
                'offset_cost_co2_only' => $this->round($offsetCost),
                'offset_cost_rf_inclusive' => $this->round($offsetCostRf),
                'per_round_trip_co2e_t' => $this->round($annualT / $trips, 2),
            ],
            'breakdown' => [
                'route' => $label !== '' ? $label : sprintf('%.0f km one-way', $dist),
                'cabin_class' => $cabin,
                'cabin_multiplier' => $cabinMult,
                'kg_co2_per_pax_km_economy_basis' => $kgPerKm,
                'round_trips' => $trips,
                'offset_tier_price' => $price,
                'formula' => 'CO₂e ≈ distance × kg/pax-km × cabin multiplier × 2 × trips; RF row × ~1.9 (Lee et al. sensitivity)',
            ],
            'units' => [
                'annual_co2e_tonnes' => 't CO₂e/yr',
                'annual_co2e_with_radiative_forcing_t' => 't CO₂e/yr',
                'cabin_penalty_vs_economy_t' => 't CO₂e/yr',
                'offset_cost_co2_only' => 'currency',
                'offset_cost_rf_inclusive' => 'currency',
                'per_round_trip_co2e_t' => 't CO₂e',
            ],
        ];
    }
}
