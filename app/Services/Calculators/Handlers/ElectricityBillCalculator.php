<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Electricity Bill Calculator
 */
class ElectricityBillCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'electricity_bill_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('units', 'Units Consumed (kWh)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 200]),
            $this->field('rate_per_unit', 'Rate Per Unit', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'unit' => 'currency']),
            $this->field('fixed_charge', 'Fixed Charge', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $units = $this->requireNumeric($inputs, 'units');
        $rate = $this->requireNumeric($inputs, 'rate_per_unit');
        $fixed = $this->requireNumeric($inputs, 'fixed_charge');
        $energy = $units * $rate;
        return [
            'results' => [
                'energy_charge' => $this->round($energy),
                'total_bill' => $this->round($energy + $fixed),
            ],
            'breakdown' => ['units' => $units],
            'units' => ['energy_charge' => 'currency', 'total_bill' => 'currency'],
        ];
    }
}
