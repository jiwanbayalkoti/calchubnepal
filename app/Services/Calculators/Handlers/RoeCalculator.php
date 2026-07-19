<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * ROE Calculator
 */
class RoeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'roe_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('net_income', 'Net Income', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 250000, 'unit' => 'currency']),
            $this->field('equity', 'Shareholder Equity', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000000, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $roe = $this->percentageOf($this->requireNumeric($inputs, 'net_income'), $this->requireNumeric($inputs, 'equity'));
        return [
            'results' => ['roe_percent' => $this->round($roe, 2)],
            'breakdown' => ['formula' => 'ROE = Net Income / Equity × 100'],
            'units' => ['roe_percent' => '%'],
        ];
    }
}
