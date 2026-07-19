<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Device Battery Life Calculator
 */
class BatteryLifeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'battery_life_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('capacity_mah', 'Battery Capacity', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000, 'unit' => 'mAh']),
            $this->field('load_ma', 'Average Load', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 500, 'unit' => 'mA']),
            $this->field('efficiency', 'Efficiency', 'number', ['min' => 50, 'max' => 100, 'step' => 0.01, 'default' => 90, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $hours = $this->safeDivide(
            $this->requireNumeric($inputs, 'capacity_mah') * ($this->requireNumeric($inputs, 'efficiency') / 100),
            $this->requireNumeric($inputs, 'load_ma')
        );
        return [
            'results' => ['estimated_hours' => $this->round($hours, 2), 'estimated_days' => $this->round($hours / 24, 2)],
            'breakdown' => ['formula' => 'hours = (mAh × efficiency) / mA'],
            'units' => ['estimated_hours' => 'hours', 'estimated_days' => 'days'],
        ];
    }
}
