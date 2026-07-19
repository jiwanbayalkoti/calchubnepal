<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Multi-mode percentage calculator supporting three common operations:
 * - percent_of: X% of Y
 * - value_is_what_percent: X is what percent of Y
 * - percentage_change: percentage change from X to Y
 */
class PercentageCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'percentage_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('mode', 'Calculation Mode', 'select', [
                'options' => [
                    'percent_of' => 'X% of Y',
                    'value_is_what_percent' => 'X is what percent of Y',
                    'percentage_change' => 'Percentage change from X to Y',
                ],
                'default' => 'percent_of',
            ]),
            $this->field('value_x', 'Value X', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 20]),
            $this->field('value_y', 'Value Y', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 200]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $mode = $this->toString($inputs, 'mode', 'percent_of');
        $x = $this->requireNumeric($inputs, 'value_x');
        $y = $this->requireNumeric($inputs, 'value_y');

        $result = match ($mode) {
            'value_is_what_percent' => $this->percentageOf($x, $y),
            'percentage_change' => $this->percentageOf($y - $x, abs($x)),
            default => $y * ($x / 100),
        };

        $label = match ($mode) {
            'value_is_what_percent' => 'percent',
            'percentage_change' => 'percentage_change',
            default => 'value',
        };

        return [
            'results' => [
                $label => $this->round($result),
            ],
            'breakdown' => [
                'mode' => $mode,
                'value_x' => $x,
                'value_y' => $y,
            ],
            'units' => [
                $label => $mode === 'percent_of' ? 'number' : '%',
            ],
        ];
    }
}
