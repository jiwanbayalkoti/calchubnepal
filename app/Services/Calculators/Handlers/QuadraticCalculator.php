<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Quadratic Equation Calculator
 */
class QuadraticCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'quadratic_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('a', 'Coefficient a', 'number', ['min' => -1000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('b', 'Coefficient b', 'number', ['min' => -1000000, 'max' => 1000000000, 'step' => 0.01, 'default' => -5]),
            $this->field('c', 'Coefficient c', 'number', ['min' => -1000000, 'max' => 1000000000, 'step' => 0.01, 'default' => 6]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $c = $this->requireNumeric($inputs, 'c');
        if ($a == 0.0) {
            throw new InvalidArgumentException('Coefficient a cannot be zero for a quadratic equation.');
        }
        $disc = ($b ** 2) - (4 * $a * $c);
        $results = ['discriminant' => $this->round($disc, 6)];
        if ($disc > 0) {
            $results['root_1'] = $this->round((-$b + sqrt($disc)) / (2 * $a), 6);
            $results['root_2'] = $this->round((-$b - sqrt($disc)) / (2 * $a), 6);
        } elseif ($disc == 0.0) {
            $results['root'] = $this->round(-$b / (2 * $a), 6);
        } else {
            $real = -$b / (2 * $a);
            $imag = sqrt(abs($disc)) / (2 * $a);
            $results['root_1'] = $this->round($real, 6).' + '.$this->round($imag, 6).'i';
            $results['root_2'] = $this->round($real, 6).' - '.$this->round($imag, 6).'i';
        }
        return [
            'results' => $results,
            'breakdown' => ['equation' => "{$a}x² + {$b}x + {$c} = 0"],
            'units' => ['discriminant' => 'number', 'root_1' => 'x', 'root_2' => 'x', 'root' => 'x'],
        ];
    }
}
