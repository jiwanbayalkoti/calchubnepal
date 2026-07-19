<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * LCM Calculator
 */
class LcmCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'lcm_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('a', 'Number A', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 12]),
            $this->field('b', 'Number B', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 18]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $x = abs((int) $this->requireNumeric($inputs, 'a'));
        $y = abs((int) $this->requireNumeric($inputs, 'b'));
        $a = $x; $b = $y;
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        $gcd = $a;
        $lcm = (int) (($x / $gcd) * $y);
        return [
            'results' => ['lcm' => $lcm, 'gcd' => $gcd],
            'breakdown' => ['formula' => 'LCM = |a·b| / GCD'],
            'units' => ['lcm' => 'integer', 'gcd' => 'integer'],
        ];
    }
}
