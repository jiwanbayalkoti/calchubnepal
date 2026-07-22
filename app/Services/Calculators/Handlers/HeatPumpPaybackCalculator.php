<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Heat Pump Payback Calculator
 * Converts heating bill → BTUs, applies climate-zone COP, payback + fuel-trajectory row.
 */
class HeatPumpPaybackCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'heat_pump_payback_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('current_fuel', 'Current Heating Fuel', 'select', [
                'options' => [
                    'gas' => 'Natural gas',
                    'propane' => 'Propane',
                    'oil' => 'Heating oil',
                    'electric_resistance' => 'Electric resistance',
                ],
                'default' => 'gas',
            ]),
            $this->field('annual_heating_bill', 'Annual Heating Bill', 'number', ['min' => 100, 'max' => 20000, 'step' => 50, 'default' => 1800, 'unit' => 'currency']),
            $this->field('fuel_unit_price', 'Fuel Unit Price', 'number', ['min' => 0.01, 'max' => 10, 'step' => 0.01, 'default' => 1.40, 'unit' => 'currency/unit']),
            $this->field('climate_zone', 'Climate Zone', 'select', [
                'options' => [
                    'mild' => 'Mild (COP ~3.5)',
                    'mixed' => 'Mixed (COP ~2.8)',
                    'cold' => 'Cold (COP ~2.2)',
                ],
                'default' => 'mixed',
            ]),
            $this->field('home_sqft', 'Home Size', 'number', ['min' => 400, 'max' => 10000, 'step' => 50, 'default' => 1800, 'unit' => 'sq.ft']),
            $this->field('insulation', 'Insulation Rating', 'select', [
                'options' => ['poor' => 'Poor', 'average' => 'Average', 'good' => 'Good'],
                'default' => 'average',
            ]),
            $this->field('electric_rate', 'Electricity Rate', 'number', ['min' => 0.05, 'max' => 0.6, 'step' => 0.01, 'default' => 0.14, 'unit' => 'currency/kWh']),
            $this->field('install_cost', 'Heat Pump Install Cost', 'number', ['min' => 2000, 'max' => 40000, 'step' => 100, 'default' => 12000, 'unit' => 'currency']),
            $this->field('tax_credit', 'Tax Credit / Rebate (25C / HEEHRA)', 'number', ['min' => 0, 'max' => 15000, 'step' => 100, 'default' => 2000, 'unit' => 'currency', 'required' => false]),
            $this->field('fuel_growth', 'Fuel Price Growth', 'number', ['min' => 0, 'max' => 15, 'step' => 0.5, 'default' => 4, 'unit' => '%/yr', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $fuel = $this->toString($inputs, 'current_fuel', 'gas');
        $bill = $this->requireNumeric($inputs, 'annual_heating_bill');
        $unitPrice = $this->requireNumeric($inputs, 'fuel_unit_price');
        $zone = $this->toString($inputs, 'climate_zone', 'mixed');
        $sqft = $this->requireNumeric($inputs, 'home_sqft');
        $insul = $this->toString($inputs, 'insulation', 'average');
        $elec = $this->requireNumeric($inputs, 'electric_rate');
        $install = $this->requireNumeric($inputs, 'install_cost');
        $credit = $this->toFloat($inputs, 'tax_credit', 2000);
        $fuelGrowth = $this->toFloat($inputs, 'fuel_growth', 4) / 100;

        // BTU per fuel unit (approx)
        $btuPerUnit = match ($fuel) {
            'propane' => 91500,
            'oil' => 138500,
            'electric_resistance' => 3412, // per kWh
            default => 100000, // therm approx
        };
        $units = $this->safeDivide($bill, $unitPrice);
        $btuDelivered = $units * $btuPerUnit * 0.8; // AFUE ~80% for fossil

        $cop = match ($zone) {
            'mild' => 3.5,
            'cold' => 2.2,
            default => 2.8,
        };
        $hpKwh = $this->safeDivide($btuDelivered, 3412 * $cop);
        $hpCost = $hpKwh * $elec;
        $annualSavings = max(0, $bill - $hpCost);
        $netInstall = max(0, $install - $credit);
        $payback = $annualSavings > 0 ? $netInstall / $annualSavings : null;

        // Fuel-trajectory accelerated: savings grow with fuel price
        $cum = -$netInstall;
        $accelPayback = null;
        for ($y = 1; $y <= 25; $y++) {
            $fuelBillY = $bill * ((1 + $fuelGrowth) ** ($y - 1));
            $saveY = max(0, $fuelBillY - $hpCost);
            $cum += $saveY;
            if ($accelPayback === null && $cum >= 0) {
                $accelPayback = $y;
            }
        }

        $normBtuPerSqft = match ($zone) {
            'mild' => 15000,
            'cold' => 45000,
            default => 30000,
        };
        $insFactor = match ($insul) {
            'poor' => 1.25,
            'good' => 0.85,
            default => 1.0,
        };
        $implied = $this->safeDivide($btuDelivered, $sqft);
        $hint = $implied > $normBtuPerSqft * $insFactor
            ? 'Heating demand looks high for your climate — an insulation upgrade may cut heat-pump size and improve payback.'
            : 'Demand looks in a normal band for this climate/insulation tier.';

        return [
            'results' => [
                'heat_pump_annual_operating_cost' => $this->round($hpCost),
                'annual_savings' => $this->round($annualSavings),
                'simple_payback_years' => $payback === null ? 'n/a' : $this->round($payback, 1),
                'fuel_trajectory_payback_years' => $accelPayback === null ? 'Beyond 25 years' : $accelPayback,
                'seasonal_cop_used' => $cop,
                'net_install_after_credit' => $this->round($netInstall),
                'insulation_opportunity_hint' => $hint,
            ],
            'breakdown' => [
                'btu_delivered_current' => $this->round($btuDelivered),
                'hp_kwh' => $this->round($hpKwh),
                'anchors' => 'DOE EnergyStar / AHRI 210/240 COP bands, EIA fuel+power, IRS §25C / IRA HEEHRA-style credits',
            ],
            'units' => [
                'heat_pump_annual_operating_cost' => 'currency/yr',
                'annual_savings' => 'currency/yr',
                'simple_payback_years' => 'years',
                'fuel_trajectory_payback_years' => 'years',
                'seasonal_cop_used' => 'COP',
                'net_install_after_credit' => 'currency',
                'insulation_opportunity_hint' => '',
            ],
        ];
    }
}
