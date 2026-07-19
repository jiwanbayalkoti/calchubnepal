<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Protein Intake Calculator
 */
class ProteinIntakeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'protein_intake_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight_kg', 'Body Weight', 'number', ['min' => 20, 'max' => 300, 'step' => 0.01, 'default' => 70, 'unit' => 'kg']),
            $this->field('goal', 'Goal', 'select', ['options' => ['sedentary' => 'Sedentary (0.8 g/kg)', 'active' => 'Active (1.2 g/kg)', 'muscle' => 'Muscle Gain (1.6–2.2 g/kg)', 'athlete' => 'Athlete (2.0 g/kg)'], 'default' => 'active']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $w = $this->requireNumeric($inputs, 'weight_kg');
        [$min, $max] = match ($this->toString($inputs, 'goal', 'active')) {
            'sedentary' => [0.8, 0.8],
            'muscle' => [1.6, 2.2],
            'athlete' => [2.0, 2.2],
            default => [1.2, 1.6],
        };
        return [
            'results' => [
                'protein_min_g' => $this->round($w * $min, 1),
                'protein_max_g' => $this->round($w * $max, 1),
            ],
            'breakdown' => ['weight_kg' => $w],
            'units' => ['protein_min_g' => 'g/day', 'protein_max_g' => 'g/day'],
        ];
    }
}
