<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * True Cost Per Mile Calculator
 * Fuel + insurance + maintenance + depreciation, compared to the IRS standard rate.
 */
class TrueCostPerMileCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'true_cost_per_mile_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('annual_miles', 'Annual Miles Driven', 'number', ['min' => 1, 'max' => 200000, 'step' => 100, 'default' => 12000, 'unit' => 'mi/yr']),
            $this->field('mpg', 'Fuel Economy', 'number', ['min' => 1, 'max' => 150, 'step' => 0.1, 'default' => 28, 'unit' => 'mpg']),
            $this->field('fuel_price', 'Fuel Price / Gallon', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 3.50, 'unit' => 'currency']),
            $this->field('annual_insurance', 'Annual Insurance', 'number', ['min' => 0, 'max' => 50000, 'step' => 1, 'default' => 1600, 'unit' => 'currency']),
            $this->field('annual_maintenance', 'Annual Maintenance / Repairs', 'number', ['min' => 0, 'max' => 50000, 'step' => 1, 'default' => 900, 'unit' => 'currency']),
            $this->field('purchase_price', 'Purchase Price (or Current Value)', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 28000, 'unit' => 'currency']),
            $this->field('ownership_years', 'Ownership Period', 'number', ['min' => 0.5, 'max' => 30, 'step' => 0.5, 'default' => 5, 'unit' => 'years']),
            $this->field('residual_value', 'Expected Residual / Resale Value', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 12000, 'unit' => 'currency']),
            $this->field('irs_rate', 'IRS Standard Mileage Rate', 'number', ['min' => 0, 'max' => 5, 'step' => 0.01, 'default' => 0.67, 'unit' => 'currency/mi', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $miles = $this->requireNumeric($inputs, 'annual_miles');
        $mpg = $this->requireNumeric($inputs, 'mpg');
        $fuelPrice = $this->requireNumeric($inputs, 'fuel_price');
        $insurance = $this->requireNumeric($inputs, 'annual_insurance');
        $maintenance = $this->requireNumeric($inputs, 'annual_maintenance');
        $purchase = $this->requireNumeric($inputs, 'purchase_price');
        $years = max(0.5, $this->requireNumeric($inputs, 'ownership_years'));
        $residual = $this->requireNumeric($inputs, 'residual_value');
        $irsRate = $this->toFloat($inputs, 'irs_rate', 0.67);

        $annualFuel = $this->safeDivide($miles, $mpg) * $fuelPrice;
        $annualDepreciation = $this->safeDivide(max(0, $purchase - $residual), $years);
        $annualTotal = $annualFuel + $insurance + $maintenance + $annualDepreciation;
        $trueCostPerMile = $this->safeDivide($annualTotal, $miles);
        $fuelOnlyPerMile = $this->safeDivide($annualFuel, $miles);
        $underestimatePct = $this->safeDivide($trueCostPerMile - $fuelOnlyPerMile, $trueCostPerMile) * 100;
        $vsIrs = $trueCostPerMile - $irsRate;
        $annualVsIrs = $vsIrs * $miles;

        $verdict = abs($vsIrs) < 0.02
            ? 'About even with the IRS rate'
            : ($vsIrs > 0
                ? 'True cost is ABOVE the IRS standard rate'
                : 'True cost is BELOW the IRS standard rate');

        return [
            'results' => [
                'true_cost_per_mile' => $this->round($trueCostPerMile, 3),
                'fuel_only_per_mile' => $this->round($fuelOnlyPerMile, 3),
                'underestimate_if_fuel_only_pct' => $this->round($underestimatePct, 1),
                'annual_true_cost' => $this->round($annualTotal),
                'vs_irs_per_mile' => $this->round($vsIrs, 3),
                'annual_vs_irs' => $this->round($annualVsIrs),
                'verdict' => $verdict,
            ],
            'breakdown' => [
                'annual_fuel' => $this->round($annualFuel),
                'annual_insurance' => $this->round($insurance),
                'annual_maintenance' => $this->round($maintenance),
                'annual_depreciation' => $this->round($annualDepreciation),
                'irs_rate' => $this->round($irsRate, 3),
                'formula' => '(fuel + insurance + maintenance + depreciation) ÷ annual miles',
            ],
            'units' => [
                'true_cost_per_mile' => 'currency/mi',
                'fuel_only_per_mile' => 'currency/mi',
                'underestimate_if_fuel_only_pct' => '%',
                'annual_true_cost' => 'currency',
                'vs_irs_per_mile' => 'currency/mi',
                'annual_vs_irs' => 'currency',
                'verdict' => '',
            ],
        ];
    }
}
