<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Body Mass Index calculator: BMI = weight (kg) / height (m)².
 * Supports metric (kg, cm) and imperial (lb, in) unit systems.
 */
class BmiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'bmi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('unit_system', 'Unit System', 'select', [
                'options' => ['metric' => 'Metric (kg/cm)', 'imperial' => 'Imperial (lb/in)'],
                'default' => 'metric',
                'required' => false,
            ]),
            $this->field('weight', 'Weight', 'number', ['min' => 1, 'max' => 700, 'step' => 0.1, 'default' => 70]),
            $this->field('height', 'Height', 'number', ['min' => 30, 'max' => 300, 'step' => 0.1, 'default' => 170]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $unitSystem = $this->toString($inputs, 'unit_system', 'metric');
        $weight = $this->requireNumeric($inputs, 'weight');
        $height = $this->requireNumeric($inputs, 'height');

        if ($unitSystem === 'imperial') {
            $weightKg = $weight * 0.45359237;
            $heightM = ($height * 2.54) / 100;
        } else {
            $weightKg = $weight;
            $heightM = $height / 100;
        }

        $bmi = $this->safeDivide($weightKg, $heightM ** 2);

        $category = match (true) {
            $bmi < 18.5 => 'Underweight',
            $bmi < 25 => 'Normal weight',
            $bmi < 30 => 'Overweight',
            default => 'Obese',
        };

        $healthyMin = 18.5 * ($heightM ** 2);
        $healthyMax = 24.9 * ($heightM ** 2);

        return [
            'results' => [
                'bmi' => $this->round($bmi, 1),
                'category' => $category,
            ],
            'breakdown' => [
                'weight_kg' => $this->round($weightKg),
                'height_m' => $this->round($heightM, 2),
                'healthy_weight_range_min' => $this->round($healthyMin),
                'healthy_weight_range_max' => $this->round($healthyMax),
            ],
            'units' => [
                'bmi' => 'kg/m²',
                'healthy_weight_range_min' => 'kg',
                'healthy_weight_range_max' => 'kg',
            ],
        ];
    }
}
