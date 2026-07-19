<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Rental Yield Calculator
 */
class RentalYieldCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rental_yield_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('annual_rent', 'Annual Rent', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 360000, 'unit' => 'currency']),
            $this->field('property_value', 'Property Value', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000000, 'unit' => 'currency']),
            $this->field('annual_expenses', 'Annual Expenses', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 40000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $rent = $this->requireNumeric($inputs, 'annual_rent');
        $value = $this->requireNumeric($inputs, 'property_value');
        $expenses = $this->toFloat($inputs, 'annual_expenses');
        $gross = $this->percentageOf($rent, $value);
        $net = $this->percentageOf($rent - $expenses, $value);
        return [
            'results' => [
                'gross_yield_percent' => $this->round($gross, 2),
                'net_yield_percent' => $this->round($net, 2),
            ],
            'breakdown' => ['net_income' => $this->round($rent - $expenses)],
            'units' => ['gross_yield_percent' => '%', 'net_yield_percent' => '%'],
        ];
    }
}
