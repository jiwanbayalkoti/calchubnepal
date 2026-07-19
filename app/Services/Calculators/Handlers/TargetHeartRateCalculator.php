<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Target Heart Rate Calculator
 */
class TargetHeartRateCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'target_heart_rate_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('age', 'Age', 'number', ['min' => 10, 'max' => 90, 'step' => 1, 'default' => 30]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $max = 220 - $this->requireNumeric($inputs, 'age');
        return [
            'results' => [
                'max_hr' => (int) $max,
                'moderate_zone' => (int) round($max * 0.5).'–'.(int) round($max * 0.7),
                'vigorous_zone' => (int) round($max * 0.7).'–'.(int) round($max * 0.85),
            ],
            'breakdown' => ['formula' => '220 − age'],
            'units' => ['max_hr' => 'bpm', 'moderate_zone' => 'bpm', 'vigorous_zone' => 'bpm'],
        ];
    }
}
