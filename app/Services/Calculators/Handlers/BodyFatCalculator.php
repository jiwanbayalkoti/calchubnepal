<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Body fat percentage estimator using the U.S. Navy circumference method:
 * Men:   BFP = 495 / (1.0324 - 0.19077*log10(waist-neck) + 0.15456*log10(height)) - 450
 * Women: BFP = 495 / (1.29579 - 0.35004*log10(waist+hip-neck) + 0.22100*log10(height)) - 450
 * All measurements in centimetres.
 */
class BodyFatCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'body_fat_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('gender', 'Gender', 'select', [
                'options' => ['male' => 'Male', 'female' => 'Female'],
                'default' => 'male',
            ]),
            $this->field('waist', 'Waist Circumference', 'number', ['unit' => 'cm', 'min' => 30, 'max' => 300, 'step' => 0.1, 'default' => 85]),
            $this->field('neck', 'Neck Circumference', 'number', ['unit' => 'cm', 'min' => 10, 'max' => 100, 'step' => 0.1, 'default' => 38]),
            $this->field('height', 'Height', 'number', ['unit' => 'cm', 'min' => 30, 'max' => 300, 'step' => 0.1, 'default' => 175]),
            $this->field('hip', 'Hip Circumference (Female only)', 'number', ['unit' => 'cm', 'min' => 0, 'max' => 300, 'step' => 0.1, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $gender = $this->toString($inputs, 'gender', 'male');
        $waist = $this->requireNumeric($inputs, 'waist');
        $neck = $this->requireNumeric($inputs, 'neck');
        $height = $this->requireNumeric($inputs, 'height');
        $hip = $this->toFloat($inputs, 'hip', 0);

        if ($gender === 'female') {
            $circumferenceTerm = $waist + $hip - $neck;
            $bodyFatPercent = 495 / (1.29579 - (0.35004 * log10($circumferenceTerm)) + (0.221 * log10($height))) - 450;
        } else {
            $circumferenceTerm = $waist - $neck;
            $bodyFatPercent = 495 / (1.0324 - (0.19077 * log10($circumferenceTerm)) + (0.15456 * log10($height))) - 450;
        }

        $category = match (true) {
            $gender === 'female' && $bodyFatPercent < 21 => 'Athletic / Essential Fat',
            $gender === 'female' && $bodyFatPercent < 33 => 'Fitness / Acceptable',
            $gender === 'female' => 'Obese',
            $bodyFatPercent < 14 => 'Athletic / Essential Fat',
            $bodyFatPercent < 25 => 'Fitness / Acceptable',
            default => 'Obese',
        };

        return [
            'results' => [
                'body_fat_percent' => $this->round(max(0, $bodyFatPercent), 1),
                'category' => $category,
            ],
            'breakdown' => [
                'circumference_term' => $this->round($circumferenceTerm, 2),
                'gender' => $gender,
            ],
            'units' => [
                'body_fat_percent' => '%',
            ],
        ];
    }
}
