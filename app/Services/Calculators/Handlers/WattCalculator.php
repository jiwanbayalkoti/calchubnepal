<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Watt Calculator
 */
class WattCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'watt_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('voltage', 'Voltage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 220, 'unit' => 'V']),
            $this->field('current', 'Current', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5, 'unit' => 'A']),
            $this->field('power_factor', 'Power Factor', 'number', ['min' => 0.1, 'max' => 1, 'step' => 0.01, 'default' => 1]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $watts = $this->requireNumeric($inputs, 'voltage') * $this->requireNumeric($inputs, 'current') * $this->requireNumeric($inputs, 'power_factor');
        return [
            'results' => ['watts' => $this->round($watts, 2), 'kilowatts' => $this->round($watts / 1000, 4)],
            'breakdown' => ['formula' => 'P = V × I × PF'],
            'units' => ['watts' => 'W', 'kilowatts' => 'kW'],
        ];
    }
}
