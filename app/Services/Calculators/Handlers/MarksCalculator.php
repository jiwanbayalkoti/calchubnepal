<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Marks Calculator
 */
class MarksCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'marks_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('obtained', 'Marks Obtained', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 420]),
            $this->field('maximum', 'Maximum Marks', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 500]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $got = $this->requireNumeric($inputs, 'obtained');
        $max = $this->requireNumeric($inputs, 'maximum');
        return [
            'results' => [
                'percentage' => $this->round($this->percentageOf($got, $max), 2),
                'marks_needed_for_90' => max(0, $this->round(($max * 0.9) - $got, 1)),
            ],
            'breakdown' => ['obtained' => $got, 'maximum' => $max],
            'units' => ['percentage' => '%', 'marks_needed_for_90' => 'marks'],
        ];
    }
}
