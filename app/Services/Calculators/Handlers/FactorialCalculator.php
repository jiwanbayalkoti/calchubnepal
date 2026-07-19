<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Factorial Calculator
 */
class FactorialCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'factorial_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('n', 'n', 'number', ['min' => 0, 'max' => 170, 'step' => 1, 'default' => 5]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $n = (int) $this->requireNumeric($inputs, 'n');
        if ($n < 0) {
            throw new InvalidArgumentException('n must be non-negative.');
        }
        $result = 1.0;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }
        return [
            'results' => ['factorial' => $result],
            'breakdown' => ['expression' => $n.'!'],
            'units' => ['factorial' => 'number'],
        ];
    }
}
