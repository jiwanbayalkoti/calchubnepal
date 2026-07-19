<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * TDEE Calculator
 */
class TdeeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tdee_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight_kg', 'Weight', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 70, 'unit' => 'kg']),
            $this->field('height_cm', 'Height', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 170, 'unit' => 'cm']),
            $this->field('age', 'Age', 'number', ['min' => 15, 'max' => 90, 'step' => 1, 'default' => 30]),
            $this->field('gender', 'Gender', 'select', ['options' => ['male' => 'Male', 'female' => 'Female'], 'default' => 'male']),
            $this->field('activity', 'Activity Level', 'select', ['options' => ['1.2' => 'Sedentary', '1.375' => 'Lightly active', '1.55' => 'Moderately active', '1.725' => 'Very active', '1.9' => 'Extra active'], 'default' => '1.55']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $h = $this->requireNumeric($inputs, 'height_cm');
        $age = $this->requireNumeric($inputs, 'age');
        $bmr = $this->toString($inputs, 'gender', 'male') === 'female'
            ? (10 * $w) + (6.25 * $h) - (5 * $age) - 161
            : (10 * $w) + (6.25 * $h) - (5 * $age) + 5;
        $factor = (float) $this->toString($inputs, 'activity', '1.55');
        $tdee = $bmr * $factor;
        return [
            'results' => [
                'bmr' => $this->round($bmr),
                'tdee' => $this->round($tdee),
                'cut_calories' => $this->round($tdee - 500),
                'bulk_calories' => $this->round($tdee + 300),
            ],
            'breakdown' => ['formula' => 'Mifflin–St Jeor × activity'],
            'units' => ['bmr' => 'kcal', 'tdee' => 'kcal', 'cut_calories' => 'kcal', 'bulk_calories' => 'kcal'],
        ];
    }
}
