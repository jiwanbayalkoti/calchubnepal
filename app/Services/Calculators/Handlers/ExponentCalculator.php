<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Exponent Calculator
 */
class ExponentCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'exponent_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('base', 'Base', 'number', ['min' => -1000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 2]),
            $this->field('exponent', 'Exponent', 'number', ['min' => -100, 'max' => 100, 'step' => 0.01, 'default' => 8]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $base = $this->requireNumeric($inputs, 'base');
        $exp = $this->requireNumeric($inputs, 'exponent');
        $result = $base ** $exp;
        return [
            'results' => ['result' => $this->round($result, 8)],
            'breakdown' => ['expression' => "{$base}^{$exp}"],
            'units' => ['result' => 'number'],
        ];
    }
}
