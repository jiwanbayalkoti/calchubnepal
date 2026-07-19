<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Probability Calculator
 */
class ProbabilityCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'probability_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('favorable', 'Favorable Outcomes', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 1]),
            $this->field('total', 'Total Outcomes', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 6]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $favorable = $this->requireNumeric($inputs, 'favorable');
        $total = $this->requireNumeric($inputs, 'total');
        if ($favorable > $total) {
            throw new InvalidArgumentException('Favorable outcomes cannot exceed total outcomes.');
        }
        $p = $this->safeDivide($favorable, $total);
        return [
            'results' => [
                'probability' => $this->round($p, 6),
                'probability_percent' => $this->round($p * 100, 4),
                'odds_for' => $favorable.':'.($total - $favorable),
            ],
            'breakdown' => ['favorable' => $favorable, 'total' => $total],
            'units' => ['probability' => '0-1', 'probability_percent' => '%', 'odds_for' => 'ratio'],
        ];
    }
}
