<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Daily water intake calculator using the common 33ml per kg of body
 * weight baseline, with additional intake for exercise duration (350ml
 * per 30 minutes) and hot climates (+500ml).
 */
class WaterIntakeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'water_intake_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight_kg', 'Weight', 'number', ['unit' => 'kg', 'min' => 1, 'max' => 500, 'step' => 0.1, 'default' => 70]),
            $this->field('exercise_minutes', 'Daily Exercise Duration', 'number', ['unit' => 'minutes', 'min' => 0, 'max' => 1000, 'step' => 1, 'default' => 0, 'required' => false]),
            $this->field('climate', 'Climate', 'select', [
                'options' => ['normal' => 'Normal', 'hot' => 'Hot / Humid'],
                'default' => 'normal',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $weightKg = $this->requireNumeric($inputs, 'weight_kg');
        $exerciseMinutes = $this->toFloat($inputs, 'exercise_minutes', 0);
        $climate = $this->toString($inputs, 'climate', 'normal');

        $baseIntakeLiters = $weightKg * 0.033;
        $exerciseIntakeLiters = ($exerciseMinutes / 30) * 0.35;
        $climateIntakeLiters = $climate === 'hot' ? 0.5 : 0;

        $totalIntakeLiters = $baseIntakeLiters + $exerciseIntakeLiters + $climateIntakeLiters;

        return [
            'results' => [
                'daily_water_intake' => $this->round($totalIntakeLiters, 2),
                'daily_water_intake_glasses' => (int) ceil($totalIntakeLiters / 0.25),
            ],
            'breakdown' => [
                'base_intake' => $this->round($baseIntakeLiters, 2),
                'exercise_intake' => $this->round($exerciseIntakeLiters, 2),
                'climate_intake' => $climateIntakeLiters,
            ],
            'units' => [
                'daily_water_intake' => 'liters',
                'daily_water_intake_glasses' => 'glasses (250ml)',
            ],
        ];
    }
}
