<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Body Surface Area Calculator
 */
class BodySurfaceAreaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'body_surface_area_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('height_cm', 'Height', 'number', ['min' => 50, 'max' => 1000000000, 'step' => 0.01, 'default' => 170, 'unit' => 'cm']),
            $this->field('weight_kg', 'Weight', 'number', ['min' => 10, 'max' => 1000000000, 'step' => 0.01, 'default' => 70, 'unit' => 'kg']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $h = $this->requireNumeric($inputs, 'height_cm');
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $bsa = sqrt(($h * $w) / 3600); // Mosteller
        return [
            'results' => ['bsa_m2' => $this->round($bsa, 3)],
            'breakdown' => ['formula' => 'Mosteller: √((H×W)/3600)'],
            'units' => ['bsa_m2' => 'm²'],
        ];
    }
}
