<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * VO2 Max Estimator
 */
class Vo2MaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'vo2_max_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('age', 'Age', 'number', ['min' => 15, 'max' => 80, 'step' => 1, 'default' => 30]),
            $this->field('resting_hr', 'Resting Heart Rate', 'number', ['min' => 30, 'max' => 120, 'step' => 1, 'default' => 60]),
        ];
    }

    public function calculate(array $inputs): array
    {
        // Uth–Sørensen–Johansen estimate
        $vo2 = 15.3 * ((220 - $this->requireNumeric($inputs, 'age')) / $this->requireNumeric($inputs, 'resting_hr'));
        return [
            'results' => ['vo2_max' => $this->round($vo2, 1)],
            'breakdown' => ['method' => 'Resting HR estimate'],
            'units' => ['vo2_max' => 'mL/kg/min'],
        ];
    }
}
