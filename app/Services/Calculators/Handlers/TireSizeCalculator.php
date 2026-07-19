<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Tire Size Calculator
 */
class TireSizeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tire_size_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('width', 'Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 205, 'unit' => 'mm']),
            $this->field('aspect', 'Aspect Ratio', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 55, 'unit' => '%']),
            $this->field('rim', 'Rim Diameter', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 16, 'unit' => 'in']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $width = $this->requireNumeric($inputs, 'width');
        $aspect = $this->requireNumeric($inputs, 'aspect');
        $rim = $this->requireNumeric($inputs, 'rim');
        $sidewall = $width * ($aspect / 100);
        $diameterMm = ($rim * 25.4) + (2 * $sidewall);
        $circumference = pi() * $diameterMm;
        return [
            'results' => [
                'sidewall_mm' => $this->round($sidewall, 1),
                'overall_diameter_mm' => $this->round($diameterMm, 1),
                'circumference_m' => $this->round($circumference / 1000, 3),
            ],
            'breakdown' => ['size_code' => "{$width}/{$aspect}R{$rim}"],
            'units' => ['sidewall_mm' => 'mm', 'overall_diameter_mm' => 'mm', 'circumference_m' => 'm'],
        ];
    }
}
