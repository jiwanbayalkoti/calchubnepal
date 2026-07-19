<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Mileage Calculator
 */
class MileageCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'mileage_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance', 'Distance Travelled', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 300, 'unit' => 'km']),
            $this->field('fuel_used', 'Fuel Used', 'number', ['min' => 0.1, 'max' => 1000000000, 'step' => 0.01, 'default' => 20, 'unit' => 'L']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $mileage = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'fuel_used'));
        return [
            'results' => ['mileage_km_l' => $this->round($mileage, 2), 'l_per_100km' => $this->round(100 / $mileage, 2)],
            'breakdown' => [],
            'units' => ['mileage_km_l' => 'km/L', 'l_per_100km' => 'L/100km'],
        ];
    }
}
