<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Irrigation Water Calculator
 */
class IrrigationCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'irrigation_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('area_m2', 'Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000, 'unit' => 'm²']),
            $this->field('depth_mm', 'Irrigation Depth', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 25, 'unit' => 'mm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // 1 mm over 1 m² = 1 liter
        $liters = $this->requireNumeric($inputs, 'area_m2') * $this->requireNumeric($inputs, 'depth_mm');
        return [
            'results' => [
                'water_liters' => $this->round($liters),
                'water_m3' => $this->round($liters / 1000, 3),
            ],
            'breakdown' => ['rule' => '1 mm × 1 m² = 1 L'],
            'units' => ['water_liters' => 'L', 'water_m3' => 'm³'],
        ];
    }
}
