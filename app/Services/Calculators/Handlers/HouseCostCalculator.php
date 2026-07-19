<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * House Cost Calculator
 */
class HouseCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'house_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('built_up_area', 'Built-up Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1500, 'unit' => 'sq.ft']),
            $this->field('cost_per_sqft', 'Construction Cost / sq.ft', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2500, 'unit' => 'currency']),
            $this->field('finishing_percent', 'Finishing Contingency', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 15, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'built_up_area');
        $rate = $this->requireNumeric($inputs, 'cost_per_sqft');
        $extra = $this->requireNumeric($inputs, 'finishing_percent');
        $base = $area * $rate;
        $total = $base * (1 + $extra / 100);
        return [
            'results' => [
                'base_cost' => $this->round($base),
                'estimated_total_cost' => $this->round($total),
                'cost_per_sqft_effective' => $this->round($this->safeDivide($total, $area)),
            ],
            'breakdown' => ['built_up_area' => $area],
            'units' => ['base_cost' => 'currency', 'estimated_total_cost' => 'currency', 'cost_per_sqft_effective' => 'currency'],
        ];
    }
}
