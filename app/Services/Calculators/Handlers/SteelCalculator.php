<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Calculates the weight of reinforcement steel bars using the standard
 * unit-weight formula: w (kg/m) = d² / 162, where d is the bar diameter
 * in millimetres.
 */
class SteelCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'steel_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('bar_diameter', 'Bar Diameter', 'number', ['unit' => 'mm', 'min' => 6, 'max' => 40, 'step' => 1, 'default' => 12]),
            $this->field('bar_length', 'Length per Bar', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 100, 'step' => 0.01, 'default' => 12]),
            $this->field('number_of_bars', 'Number of Bars', 'number', ['min' => 1, 'max' => 100000, 'step' => 1, 'default' => 1]),
            $this->field('wastage_percent', 'Wastage', 'number', ['unit' => '%', 'min' => 0, 'max' => 20, 'step' => 0.5, 'default' => 3, 'required' => false]),
            $this->field('rate_per_kg', 'Rate per Kg', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $diameter = $this->requireNumeric($inputs, 'bar_diameter');
        $length = $this->requireNumeric($inputs, 'bar_length');
        $count = $this->requireNumeric($inputs, 'number_of_bars');
        $wastagePercent = $this->toFloat($inputs, 'wastage_percent', 3);
        $ratePerKg = $this->toFloat($inputs, 'rate_per_kg', 0);

        $unitWeightPerMeter = ($diameter ** 2) / 162;
        $totalLength = $length * $count;
        $baseWeight = $unitWeightPerMeter * $totalLength;
        $totalWeight = $baseWeight * (1 + $wastagePercent / 100);
        $estimatedCost = $totalWeight * $ratePerKg;

        return [
            'results' => [
                'unit_weight_per_meter' => $this->round($unitWeightPerMeter, 4),
                'total_weight' => $this->round($totalWeight),
                'estimated_cost' => $this->round($estimatedCost),
            ],
            'breakdown' => [
                'total_length' => $this->round($totalLength),
                'base_weight' => $this->round($baseWeight),
                'wastage_percent' => $wastagePercent,
            ],
            'units' => [
                'unit_weight_per_meter' => 'kg/m',
                'total_weight' => 'kg',
                'estimated_cost' => 'currency',
            ],
        ];
    }
}
