<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Logarithm Calculator
 */
class LogCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'log_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Value', 'number', ['min' => 1.0E-10, 'max' => 1000000000, 'step' => 0.01, 'default' => 100]),
            $this->field('base', 'Base', 'number', ['min' => 1.0E-10, 'max' => 1000000000, 'step' => 0.01, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $base = $this->requireNumeric($inputs, 'base');
        if ($value <= 0 || $base <= 0 || $base == 1.0) {
            throw new InvalidArgumentException('Value must be > 0 and base must be > 0 and ≠ 1.');
        }
        $result = log($value) / log($base);
        return [
            'results' => ['logarithm' => $this->round($result, 8), 'natural_log' => $this->round(log($value), 8)],
            'breakdown' => ['value' => $value, 'base' => $base],
            'units' => ['logarithm' => 'number', 'natural_log' => 'ln'],
        ];
    }
}
