<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Nepal House Construction Cost
 */
class NepalHouseCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'nepal_house_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('area_sqft', 'Built-up Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1200, 'unit' => 'sq.ft']),
            $this->field('cost_per_sqft', 'Cost Per sq.ft (NPR)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2800, 'unit' => 'NPR']),
            $this->field('finishing', 'Finishing Contingency', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 15, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $base = $this->requireNumeric($inputs, 'area_sqft') * $this->requireNumeric($inputs, 'cost_per_sqft');
        $total = $base * (1 + $this->requireNumeric($inputs, 'finishing') / 100);
        return [
            'results' => [
                'base_cost_npr' => $this->round($base),
                'estimated_total_npr' => $this->round($total),
            ],
            'breakdown' => ['note' => 'Indicative only — rates vary by city, design and materials'],
            'units' => ['base_cost_npr' => 'NPR', 'estimated_total_npr' => 'NPR'],
        ];
    }
}
