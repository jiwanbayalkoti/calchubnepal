<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Room Area Calculator
 */
class RoomAreaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'room_area_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'unit' => 'ft']),
            $this->field('width', 'Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'ft']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        return [
            'results' => [
                'area_sqft' => $this->round($area, 2),
                'area_m2' => $this->round($area * 0.092903, 2),
            ],
            'breakdown' => ['shape' => 'rectangle'],
            'units' => ['area_sqft' => 'sq.ft', 'area_m2' => 'm²'],
        ];
    }
}
