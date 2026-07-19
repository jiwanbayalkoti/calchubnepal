<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Fraction Calculator
 */
class FractionCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'fraction_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('numerator1', 'Numerator 1', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 1]),
            $this->field('denominator1', 'Denominator 1', 'number', ['min' => 1.0E-7, 'max' => 1000000000, 'step' => 1, 'default' => 2]),
            $this->field('operation', 'Operation', 'select', ['options' => ['add' => 'Add', 'subtract' => 'Subtract', 'multiply' => 'Multiply', 'divide' => 'Divide'], 'default' => 'add']),
            $this->field('numerator2', 'Numerator 2', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 1]),
            $this->field('denominator2', 'Denominator 2', 'number', ['min' => 1.0E-7, 'max' => 1000000000, 'step' => 1, 'default' => 3]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $n1 = $this->requireNumeric($inputs, 'numerator1');
        $d1 = $this->requireNumeric($inputs, 'denominator1');
        $n2 = $this->requireNumeric($inputs, 'numerator2');
        $d2 = $this->requireNumeric($inputs, 'denominator2');
        $op = $this->toString($inputs, 'operation', 'add');

        if ($d1 == 0.0 || $d2 == 0.0) {
            throw new InvalidArgumentException('Denominator cannot be zero.');
        }

        $a = $n1 / $d1;
        $b = $n2 / $d2;
        $result = match ($op) {
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $this->safeDivide($a, $b),
            default => $a + $b,
        };

        return [
            'results' => ['decimal_result' => $this->round($result, 6)],
            'breakdown' => ['fraction_1' => $n1.'/'.$d1, 'fraction_2' => $n2.'/'.$d2, 'operation' => $op],
            'units' => ['decimal_result' => 'number'],
        ];
    }
}
