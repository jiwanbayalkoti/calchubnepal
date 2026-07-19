<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Road Trip Cost Calculator
 */
class RoadTripCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'road_trip_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance', 'Round-trip Distance', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 500, 'unit' => 'km']),
            $this->field('mileage', 'Mileage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 14, 'unit' => 'km/L']),
            $this->field('fuel_price', 'Fuel Price / L', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 180, 'unit' => 'currency']),
            $this->field('other_costs', 'Toll / Food / Misc', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2000, 'unit' => 'currency']),
            $this->field('people', 'People Sharing', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 2]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $fuel = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'mileage')) * $this->requireNumeric($inputs, 'fuel_price');
        $other = $this->requireNumeric($inputs, 'other_costs');
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $total = $fuel + $other;
        return [
            'results' => [
                'fuel_cost' => $this->round($fuel),
                'total_cost' => $this->round($total),
                'cost_per_person' => $this->round($total / $people),
            ],
            'breakdown' => ['people' => $people],
            'units' => ['fuel_cost' => 'currency', 'total_cost' => 'currency', 'cost_per_person' => 'currency'],
        ];
    }
}
