<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Proportion Calculator
 */
class ProportionCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'proportion_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('a', 'A', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2]),
            $this->field('b', 'B', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 4]),
            $this->field('c', 'C', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 3]),
        ];
    }

    public function calculate(array $inputs): array
    {
        // A:B = C:D  => D = (B * C) / A
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $c = $this->requireNumeric($inputs, 'c');
        $d = $this->safeDivide($b * $c, $a);
        return [
            'results' => ['d' => $this->round($d, 6)],
            'breakdown' => ['proportion' => "{$a}:{$b} = {$c}:".$this->round($d, 6)],
            'units' => ['d' => 'number'],
        ];
    }
}
