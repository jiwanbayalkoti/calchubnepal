<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * One Rep Max Calculator
 */
class OneRepMaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'one_rep_max_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight', 'Weight Lifted', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 80, 'unit' => 'kg']),
            $this->field('reps', 'Reps Completed', 'number', ['min' => 1, 'max' => 12, 'step' => 1, 'default' => 5]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $w = $this->requireNumeric($inputs, 'weight');
        $r = $this->requireNumeric($inputs, 'reps');
        $orm = $w * (1 + $r / 30); // Epley
        return [
            'results' => [
                'one_rep_max' => $this->round($orm, 1),
                'estimate_90' => $this->round($orm * 0.9, 1),
                'estimate_80' => $this->round($orm * 0.8, 1),
                'estimate_70' => $this->round($orm * 0.7, 1),
            ],
            'breakdown' => ['formula' => 'Epley: w × (1 + r/30)'],
            'units' => ['one_rep_max' => 'kg', 'estimate_90' => 'kg', 'estimate_80' => 'kg', 'estimate_70' => 'kg'],
        ];
    }
}
