<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Water Tank Calculator
 */
class WaterTankCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'water_tank_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'unit' => 'm']),
            $this->field('width', 'Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1.5, 'unit' => 'm']),
            $this->field('height', 'Height', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1.2, 'unit' => 'm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $volumeM3 = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'height');
        $liters = $volumeM3 * 1000;
        return [
            'results' => ['volume_m3' => $this->round($volumeM3, 3), 'capacity_liters' => $this->round($liters, 1)],
            'breakdown' => ['shape' => 'rectangular'],
            'units' => ['volume_m3' => 'm³', 'capacity_liters' => 'L'],
        ];
    }
}
