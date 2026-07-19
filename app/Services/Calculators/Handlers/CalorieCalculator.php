<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Daily calorie needs calculator using the Mifflin-St Jeor BMR equation
 * multiplied by an activity factor (TDEE), then adjusted for the user's
 * goal (lose/maintain/gain weight) using a standard ±500 kcal/day
 * deficit or surplus (~0.45 kg/week change).
 */
class CalorieCalculator extends AbstractCalculatorHandler
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
        return 'calorie_calculator';
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
                    'sedentary' => 'Sedentary',
                    'light' => 'Lightly active',
                    'moderate' => 'Moderately active',
                    'active' => 'Very active',
                    'very_active' => 'Extremely active',
                ],
                'default' => 'sedentary',
                'required' => false,
            ]),
            $this->field('goal', 'Goal', 'select', [
                'options' => ['lose' => 'Lose Weight', 'maintain' => 'Maintain Weight', 'gain' => 'Gain Weight'],
                'default' => 'maintain',
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
        $goal = $this->toString($inputs, 'goal', 'maintain');

        $bmr = (10 * $weightKg) + (6.25 * $heightCm) - (5 * $age) + ($gender === 'female' ? -161 : 5);
        $multiplier = self::ACTIVITY_MULTIPLIERS[$activityLevel] ?? self::ACTIVITY_MULTIPLIERS['sedentary'];
        $maintenanceCalories = $bmr * $multiplier;

        $adjustment = match ($goal) {
            'lose' => -500,
            'gain' => 500,
            default => 0,
        };

        $goalCalories = max(1200, $maintenanceCalories + $adjustment);

        return [
            'results' => [
                'maintenance_calories' => $this->round($maintenanceCalories),
                'goal_calories' => $this->round($goalCalories),
            ],
            'breakdown' => [
                'bmr' => $this->round($bmr),
                'activity_multiplier' => $multiplier,
                'adjustment' => $adjustment,
            ],
            'units' => [
                'maintenance_calories' => 'kcal/day',
                'goal_calories' => 'kcal/day',
            ],
        ];
    }
}
