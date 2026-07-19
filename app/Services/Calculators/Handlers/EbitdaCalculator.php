<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * EBITDA Calculator
 */
class EbitdaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ebitda_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('operating_income', 'Operating Income (EBIT)', 'number', ['min' => -1000000000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 500000, 'unit' => 'currency']),
            $this->field('depreciation', 'Depreciation', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50000, 'unit' => 'currency']),
            $this->field('amortization', 'Amortization', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 20000, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $ebitda = $this->requireNumeric($inputs, 'operating_income')
            + $this->requireNumeric($inputs, 'depreciation')
            + $this->requireNumeric($inputs, 'amortization');
        return [
            'results' => ['ebitda' => $this->round($ebitda)],
            'breakdown' => ['formula' => 'EBIT + D&A'],
            'units' => ['ebitda' => 'currency'],
        ];
    }
}
