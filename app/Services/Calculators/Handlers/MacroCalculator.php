<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Macro Calculator
 */
class MacroCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'macro_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('calories', 'Daily Calories', 'number', ['min' => 800, 'max' => 1000000000, 'step' => 0.01, 'default' => 2000, 'unit' => 'kcal']),
            $this->field('protein_percent', 'Protein %', 'number', ['min' => 0, 'max' => 60, 'step' => 0.01, 'default' => 30, 'unit' => '%']),
            $this->field('carb_percent', 'Carb %', 'number', ['min' => 0, 'max' => 70, 'step' => 0.01, 'default' => 40, 'unit' => '%']),
            $this->field('fat_percent', 'Fat %', 'number', ['min' => 0, 'max' => 60, 'step' => 0.01, 'default' => 30, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $cal = $this->requireNumeric($inputs, 'calories');
        $p = $this->requireNumeric($inputs, 'protein_percent');
        $c = $this->requireNumeric($inputs, 'carb_percent');
        $f = $this->requireNumeric($inputs, 'fat_percent');
        if (abs(($p + $c + $f) - 100) > 0.5) {
            throw new InvalidArgumentException('Macro percentages must add up to 100%.');
        }
        return [
            'results' => [
                'protein_g' => $this->round(($cal * $p / 100) / 4, 1),
                'carbs_g' => $this->round(($cal * $c / 100) / 4, 1),
                'fat_g' => $this->round(($cal * $f / 100) / 9, 1),
            ],
            'breakdown' => ['calories' => $cal],
            'units' => ['protein_g' => 'g', 'carbs_g' => 'g', 'fat_g' => 'g'],
        ];
    }
}
