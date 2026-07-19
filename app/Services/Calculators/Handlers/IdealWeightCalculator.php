<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Ideal Weight Calculator
 */
class IdealWeightCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ideal_weight_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('height_cm', 'Height', 'number', ['min' => 100, 'max' => 250, 'step' => 0.01, 'default' => 170, 'unit' => 'cm']),
            $this->field('gender', 'Gender', 'select', ['options' => ['male' => 'Male', 'female' => 'Female'], 'default' => 'male']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $h = $this->requireNumeric($inputs, 'height_cm');
        $gender = $this->toString($inputs, 'gender', 'male');
        // Devine formula
        $inches = $h / 2.54;
        $base = $gender === 'female' ? 45.5 : 50;
        $ideal = $base + 2.3 * max(0, $inches - 60);
        return [
            'results' => ['ideal_weight_kg' => $this->round($ideal, 1), 'ideal_weight_lb' => $this->round($ideal * 2.20462, 1)],
            'breakdown' => ['formula' => 'Devine formula', 'gender' => $gender],
            'units' => ['ideal_weight_kg' => 'kg', 'ideal_weight_lb' => 'lb'],
        ];
    }
}
