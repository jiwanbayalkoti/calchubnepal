<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Inflation Calculator
 */
class InflationCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'inflation_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('present_value', 'Present Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
            $this->field('inflation_rate', 'Annual Inflation Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 5, 'unit' => '%']),
            $this->field('years', 'Years', 'number', ['min' => 1, 'max' => 50, 'step' => 0.01, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $pv = $this->requireNumeric($inputs, 'present_value');
        $rate = $this->requireNumeric($inputs, 'inflation_rate');
        $years = $this->requireNumeric($inputs, 'years');
        $fv = $pv * ((1 + $rate / 100) ** $years);
        return [
            'results' => [
                'future_cost' => $this->round($fv),
                'purchasing_power_loss' => $this->round($fv - $pv),
                'today_value_of_future_money' => $this->round($pv / ((1 + $rate / 100) ** $years)),
            ],
            'breakdown' => ['years' => $years, 'inflation_rate' => $rate],
            'units' => ['future_cost' => 'currency', 'purchasing_power_loss' => 'currency', 'today_value_of_future_money' => 'currency'],
        ];
    }
}
