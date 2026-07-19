<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Ratio Calculator
 */
class RatioCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ratio_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('a', 'Part A', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2]),
            $this->field('b', 'Part B', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 3]),
            $this->field('total', 'Scale To Total (optional)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $total = $this->toFloat($inputs, 'total', 0);
        $sum = $a + $b;
        if ($sum == 0.0) {
            throw new InvalidArgumentException('Parts cannot both be zero.');
        }
        $ratio = $this->safeDivide($a, $b);
        $results = [
            'ratio_a_to_b' => $this->round($ratio, 6),
            'a_percent' => $this->round($this->percentageOf($a, $sum), 2),
            'b_percent' => $this->round($this->percentageOf($b, $sum), 2),
        ];
        if ($total > 0) {
            $results['scaled_a'] = $this->round($total * ($a / $sum), 2);
            $results['scaled_b'] = $this->round($total * ($b / $sum), 2);
        }
        return [
            'results' => $results,
            'breakdown' => ['part_a' => $a, 'part_b' => $b, 'sum' => $sum],
            'units' => ['ratio_a_to_b' => 'ratio', 'a_percent' => '%', 'b_percent' => '%', 'scaled_a' => 'number', 'scaled_b' => 'number'],
        ];
    }
}
