<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Break-even Calculator
 */
class BreakEvenCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'break_even_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('fixed_costs', 'Fixed Costs', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
            $this->field('price_per_unit', 'Price Per Unit', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 50, 'unit' => 'currency']),
            $this->field('variable_cost_per_unit', 'Variable Cost Per Unit', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 30, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $fixed = $this->requireNumeric($inputs, 'fixed_costs');
        $price = $this->requireNumeric($inputs, 'price_per_unit');
        $variable = $this->requireNumeric($inputs, 'variable_cost_per_unit');
        $contribution = $price - $variable;
        if ($contribution <= 0) {
            throw new InvalidArgumentException('Price must be greater than variable cost.');
        }
        $units = $fixed / $contribution;
        return [
            'results' => [
                'break_even_units' => $this->round($units, 2),
                'break_even_revenue' => $this->round($units * $price),
                'contribution_margin' => $this->round($contribution),
            ],
            'breakdown' => ['fixed_costs' => $fixed],
            'units' => ['break_even_units' => 'units', 'break_even_revenue' => 'currency', 'contribution_margin' => 'currency'],
        ];
    }
}
