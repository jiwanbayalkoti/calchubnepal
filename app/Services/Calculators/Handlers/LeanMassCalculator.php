<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Lean Body Mass Calculator
 */
class LeanMassCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'lean_mass_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight_kg', 'Weight', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 75, 'unit' => 'kg']),
            $this->field('body_fat_percent', 'Body Fat %', 'number', ['min' => 3, 'max' => 60, 'step' => 0.01, 'default' => 20, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $bf = $this->requireNumeric($inputs, 'body_fat_percent');
        $fat = $w * $bf / 100;
        $lean = $w - $fat;
        return [
            'results' => ['lean_mass_kg' => $this->round($lean, 1), 'fat_mass_kg' => $this->round($fat, 1)],
            'breakdown' => ['body_fat_percent' => $bf],
            'units' => ['lean_mass_kg' => 'kg', 'fat_mass_kg' => 'kg'],
        ];
    }
}
