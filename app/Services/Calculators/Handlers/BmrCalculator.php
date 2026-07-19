<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Basal Metabolic Rate calculator using the Mifflin-St Jeor equation:
 * Men:   BMR = 10*weight(kg) + 6.25*height(cm) - 5*age + 5
 * Women: BMR = 10*weight(kg) + 6.25*height(cm) - 5*age - 161
 * Also computes Total Daily Energy Expenditure (TDEE) via an activity
 * multiplier.
 */
class BmrCalculator extends AbstractCalculatorHandler
{
    protected const ACTIVITY_MULTIPLIERS = [
        'sedentary' => 1.2,
        'light' => 1.375,
        'moderate' => 1.55,
        'active' => 1.725,
        'very_active' => 1.9,
    ];

    public function key(): string
    {
        return 'bmr_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('gender', 'Gender', 'select', [
                'options' => ['male' => 'Male', 'female' => 'Female'],
                'default' => 'male',
            ]),
            $this->field('weight_kg', 'Weight', 'number', ['unit' => 'kg', 'min' => 1, 'max' => 500, 'step' => 0.1, 'default' => 70]),
            $this->field('height_cm', 'Height', 'number', ['unit' => 'cm', 'min' => 30, 'max' => 300, 'step' => 0.1, 'default' => 170]),
            $this->field('age', 'Age', 'number', ['unit' => 'years', 'min' => 1, 'max' => 120, 'step' => 1, 'default' => 30]),
            $this->field('activity_level', 'Activity Level', 'select', [
                'options' => [
                    'sedentary' => 'Sedentary (little or no exercise)',
                    'light' => 'Lightly active',
                    'moderate' => 'Moderately active',
                    'active' => 'Very active',
                    'very_active' => 'Extremely active',
                ],
                'default' => 'sedentary',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $gender = $this->toString($inputs, 'gender', 'male');
        $weightKg = $this->requireNumeric($inputs, 'weight_kg');
        $heightCm = $this->requireNumeric($inputs, 'height_cm');
        $age = $this->requireNumeric($inputs, 'age');
        $activityLevel = $this->toString($inputs, 'activity_level', 'sedentary');

        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + ($gender === 'female' ? -161 : 5);

        $multiplier = self::ACTIVITY_MULTIPLIERS[$activityLevel] ?? self::ACTIVITY_MULTIPLIERS['sedentary'];
        $tdee = $bmr * $multiplier;

        return [
            'results' => [
                'bmr' => $this->round($bmr),
                'tdee' => $this->round($tdee),
            ],
            'breakdown' => [
                'activity_multiplier' => $multiplier,
                'gender' => $gender,
            ],
            'units' => [
                'bmr' => 'kcal/day',
                'tdee' => 'kcal/day',
            ],
        ];
    }
}
