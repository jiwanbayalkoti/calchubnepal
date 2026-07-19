<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Carpet Area Calculator
 */
class CarpetAreaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'carpet_area_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('built_up_area', 'Built-up Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1200, 'unit' => 'sq.ft']),
            $this->field('carpet_ratio', 'Carpet Area Ratio', 'number', ['min' => 50, 'max' => 90, 'step' => 0.01, 'default' => 70, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $built = $this->requireNumeric($inputs, 'built_up_area');
        $ratio = $this->requireNumeric($inputs, 'carpet_ratio') / 100;
        $carpet = $built * $ratio;
        return [
            'results' => [
                'carpet_area' => $this->round($carpet, 2),
                'non_carpet_area' => $this->round($built - $carpet, 2),
            ],
            'breakdown' => ['built_up_area' => $built],
            'units' => ['carpet_area' => 'sq.ft', 'non_carpet_area' => 'sq.ft'],
        ];
    }
}
