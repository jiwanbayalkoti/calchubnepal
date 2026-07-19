<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Fuel Cost Calculator
 */
class FuelCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'fuel_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance', 'Distance', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 200, 'unit' => 'km']),
            $this->field('mileage', 'Mileage', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 15, 'unit' => 'km/L']),
            $this->field('fuel_price', 'Fuel Price / L', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 180, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $liters = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'mileage'));
        $cost = $liters * $this->requireNumeric($inputs, 'fuel_price');
        return [
            'results' => ['fuel_needed_liters' => $this->round($liters, 2), 'trip_fuel_cost' => $this->round($cost)],
            'breakdown' => ['distance_km' => $this->requireNumeric($inputs, 'distance')],
            'units' => ['fuel_needed_liters' => 'L', 'trip_fuel_cost' => 'currency'],
        ];
    }
}
