<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Water Bill Calculator
 */
class WaterBillCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'water_bill_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('consumption', 'Consumption', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 15, 'unit' => 'm³']),
            $this->field('rate', 'Rate Per m³', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 25, 'unit' => 'currency']),
            $this->field('fixed_charge', 'Fixed Charge', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $usage = $this->requireNumeric($inputs, 'consumption') * $this->requireNumeric($inputs, 'rate');
        $fixed = $this->requireNumeric($inputs, 'fixed_charge');
        return [
            'results' => ['usage_charge' => $this->round($usage), 'total_bill' => $this->round($usage + $fixed)],
            'breakdown' => ['consumption_m3' => $this->requireNumeric($inputs, 'consumption')],
            'units' => ['usage_charge' => 'currency', 'total_bill' => 'currency'],
        ];
    }
}
