<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * GCD Calculator
 */
class GcdCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'gcd_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('a', 'Number A', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 48]),
            $this->field('b', 'Number B', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 18]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $a = abs((int) $this->requireNumeric($inputs, 'a'));
        $b = abs((int) $this->requireNumeric($inputs, 'b'));
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return [
            'results' => ['gcd' => $a],
            'breakdown' => ['method' => 'Euclidean algorithm'],
            'units' => ['gcd' => 'integer'],
        ];
    }
}
