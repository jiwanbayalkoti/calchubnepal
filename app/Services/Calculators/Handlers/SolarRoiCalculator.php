<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Solar ROI Calculator
 * Sizes system to usage or roof, models production with degradation + tariff
 * escalation, returns payback, 20-yr savings, IRR, battery delta, CO₂ avoided.
 * Note: IRS §25D 30% federal credit expired Dec 31 2025 — default federal credit = $0 for 2026+.
 */
class SolarRoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'solar_roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_bill', 'Monthly Electric Bill', 'number', ['min' => 1, 'max' => 5000, 'step' => 1, 'default' => 180, 'unit' => 'currency']),
            $this->field('kwh_rate', 'Electricity Rate', 'number', ['min' => 0.01, 'max' => 1, 'step' => 0.001, 'default' => 0.16, 'unit' => 'currency/kWh']),
            $this->field('roof_sqft', 'Usable Roof Area', 'number', ['min' => 50, 'max' => 10000, 'step' => 10, 'default' => 600, 'unit' => 'sq.ft']),
            $this->field('sun_hours', 'Peak Sun Hours (NREL-style)', 'number', ['min' => 2, 'max' => 7, 'step' => 0.1, 'default' => 4.8, 'unit' => 'hrs/day']),
            $this->field('panel_tier', 'Panel Tier', 'select', [
                'options' => ['economy' => 'Economy (~$2.60/W)', 'standard' => 'Standard (~$3.00/W)', 'premium' => 'Premium (~$3.50/W)'],
                'default' => 'standard',
            ]),
            $this->field('include_battery', 'Add Battery Storage?', 'select', [
                'options' => ['no' => 'No battery', 'yes' => 'Yes (~13.5 kWh)'],
                'default' => 'no',
            ]),
            $this->field('battery_cost', 'Battery Installed Cost', 'number', ['min' => 0, 'max' => 30000, 'step' => 100, 'default' => 12000, 'unit' => 'currency', 'required' => false]),
            $this->field('federal_credit_pct', 'Federal Credit % (0 for 2026+ installs)', 'number', ['min' => 0, 'max' => 30, 'step' => 1, 'default' => 0, 'unit' => '%', 'required' => false]),
            $this->field('state_incentive', 'State / Utility Incentive', 'number', ['min' => 0, 'max' => 50000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('financing_apr', 'Loan APR (0 = cash)', 'number', ['min' => 0, 'max' => 20, 'step' => 0.1, 'default' => 0, 'unit' => '%', 'required' => false]),
            $this->field('net_metering', 'Net-Metering Credit Factor', 'number', ['min' => 0.2, 'max' => 1, 'step' => 0.05, 'default' => 1.0, 'unit' => '× retail', 'required' => false]),
            $this->field('price_escalation', 'Electricity Price Appreciation', 'number', ['min' => 0, 'max' => 10, 'step' => 0.1, 'default' => 3.0, 'unit' => '%/yr', 'required' => false]),
            $this->field('degradation', 'Panel Degradation', 'number', ['min' => 0.2, 'max' => 1.5, 'step' => 0.05, 'default' => 0.5, 'unit' => '%/yr', 'required' => false]),
            $this->field('horizon_years', 'Analysis Horizon', 'number', ['min' => 5, 'max' => 30, 'step' => 1, 'default' => 20, 'unit' => 'years']),
            $this->field('grid_kg_co2_per_kwh', 'Grid Emissions Factor', 'number', ['min' => 0.1, 'max' => 1.2, 'step' => 0.01, 'default' => 0.4, 'unit' => 'kg CO₂/kWh', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $bill = $this->requireNumeric($inputs, 'monthly_bill');
        $rate = $this->requireNumeric($inputs, 'kwh_rate');
        $roof = $this->requireNumeric($inputs, 'roof_sqft');
        $sun = $this->requireNumeric($inputs, 'sun_hours');
        $tier = $this->toString($inputs, 'panel_tier', 'standard');
        $wantBattery = $this->toString($inputs, 'include_battery', 'no') === 'yes';
        $batteryCost = $this->toFloat($inputs, 'battery_cost', 12000);
        $fedPct = $this->toFloat($inputs, 'federal_credit_pct', 0) / 100;
        $stateInc = $this->toFloat($inputs, 'state_incentive', 0);
        $apr = $this->toFloat($inputs, 'financing_apr', 0);
        $nem = $this->toFloat($inputs, 'net_metering', 1.0);
        $esc = $this->toFloat($inputs, 'price_escalation', 3) / 100;
        $deg = $this->toFloat($inputs, 'degradation', 0.5) / 100;
        $years = (int) max(5, round($this->requireNumeric($inputs, 'horizon_years')));
        $co2Factor = $this->toFloat($inputs, 'grid_kg_co2_per_kwh', 0.4);

        $costPerW = match ($tier) {
            'economy' => 2.60,
            'premium' => 3.50,
            default => 3.00,
        };

        $monthlyKwh = $this->safeDivide($bill, $rate);
        $annualKwhNeed = $monthlyKwh * 12;
        // Usage-sized DC kW (derate ~0.82 system efficiency).
        $kwFromUsage = $this->safeDivide($annualKwhNeed, $sun * 365 * 0.82);
        // Roof limit ~15 W/sq.ft usable.
        $kwFromRoof = ($roof * 15) / 1000;
        $systemKw = min($kwFromUsage, $kwFromRoof);
        $systemKw = max(1.0, round($systemKw * 2) / 2); // 0.5 kW steps

        $grossCost = $systemKw * 1000 * $costPerW;
        $batteryAdd = $wantBattery ? $batteryCost : 0.0;
        $preIncentive = $grossCost + $batteryAdd;
        $federalCredit = $preIncentive * $fedPct;
        $netCost = max(0, $preIncentive - $federalCredit - $stateInc);

        $year1Prod = $systemKw * $sun * 365 * 0.82;
        $cashflows = [-$netCost];
        $cum = -$netCost;
        $payback = null;
        $savings20 = 0.0;
        $lifetimeKwh = 0.0;

        for ($y = 1; $y <= $years; $y++) {
            $prod = $year1Prod * ((1 - $deg) ** ($y - 1));
            $tariff = $rate * ((1 + $esc) ** ($y - 1));
            $value = $prod * $tariff * $nem;
            // Simple loan interest drag if financed (interest-only approx on declining balance ignored — use APR × remaining).
            if ($apr > 0 && $y <= 12) {
                $value -= $netCost * ($apr / 100) * 0.5 / 12; // rough average interest hit first 12 yrs amortized loosely
            }
            $cashflows[] = $value;
            $lifetimeKwh += $prod;
            $cum += $value;
            if ($y <= 20) {
                $savings20 += $value;
            }
            if ($payback === null && $cum >= 0) {
                $payback = $y;
            }
        }

        $irr = $this->irr($cashflows);
        $co2Tons = ($lifetimeKwh * $co2Factor) / 1000;

        // Battery payback delta: value of shifting ~4 kWh/day from peak (+$0.15/kWh assumed spread).
        $batteryAnnual = $wantBattery ? 4 * 365 * 0.15 : 0.0;
        $batteryPayback = ($wantBattery && $batteryAnnual > 0)
            ? $this->safeDivide($batteryCost * (1 - $fedPct), $batteryAnnual)
            : null;

        return [
            'results' => [
                'system_size_kw' => $this->round($systemKw, 1),
                'gross_installed_cost' => $this->round($preIncentive),
                'net_cost_after_incentives' => $this->round($netCost),
                'year1_production_kwh' => $this->round($year1Prod),
                'payback_years' => $payback === null ? 'Beyond horizon' : $payback,
                'savings_20yr' => $this->round(min($savings20, array_sum(array_slice($cashflows, 1, 20)))),
                'irr_pct' => $irr === null ? 'n/a' : $this->round($irr * 100, 1),
                'battery_payback_years' => $batteryPayback === null ? 'n/a' : $this->round($batteryPayback, 1),
                'lifetime_co2_avoided_tons' => $this->round($co2Tons, 1),
            ],
            'breakdown' => [
                'monthly_kwh' => $this->round($monthlyKwh),
                'kw_capped_by' => $kwFromUsage <= $kwFromRoof ? 'usage' : 'roof',
                'federal_credit_applied' => $this->round($federalCredit),
                'state_incentive' => $this->round($stateInc),
                'note' => 'IRS §25D 30% Residential Clean Energy Credit expired Dec 31 2025 — federal default is $0 for 2026 installs.',
                'anchors' => 'NREL PVWatts-style insolation, LBNL ~0.5%/yr degradation, EIA grid emissions factor',
            ],
            'units' => [
                'system_size_kw' => 'kW',
                'gross_installed_cost' => 'currency',
                'net_cost_after_incentives' => 'currency',
                'year1_production_kwh' => 'kWh',
                'payback_years' => 'years',
                'savings_20yr' => 'currency',
                'irr_pct' => '%',
                'battery_payback_years' => 'years',
                'lifetime_co2_avoided_tons' => 't CO₂',
            ],
        ];
    }

    /**
     * @param  array<int, float>  $cashflows
     */
    protected function irr(array $cashflows, float $guess = 0.1): ?float
    {
        $rate = $guess;
        for ($i = 0; $i < 50; $i++) {
            $npv = 0.0;
            $dNpv = 0.0;
            foreach ($cashflows as $t => $cf) {
                $den = (1 + $rate) ** $t;
                $npv += $cf / $den;
                if ($t > 0) {
                    $dNpv -= $t * $cf / ((1 + $rate) ** ($t + 1));
                }
            }
            if (abs($dNpv) < 1e-9) {
                break;
            }
            $new = $rate - $npv / $dNpv;
            if (! is_finite($new) || $new <= -0.99) {
                return null;
            }
            if (abs($new - $rate) < 1e-7) {
                return $new;
            }
            $rate = $new;
        }

        return is_finite($rate) ? $rate : null;
    }
}
