<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Tip Calculator
 */
class TipCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tip_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('bill', 'Bill Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1500, 'unit' => 'currency']),
            $this->field('tip_percent', 'Tip %', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 10, 'unit' => '%']),
            $this->field('people', 'Split Between', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 1]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $bill = $this->requireNumeric($inputs, 'bill');
        $tip = $bill * $this->requireNumeric($inputs, 'tip_percent') / 100;
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $total = $bill + $tip;
        return [
            'results' => [
                'tip_amount' => $this->round($tip),
                'total_with_tip' => $this->round($total),
                'per_person' => $this->round($total / $people),
            ],
            'breakdown' => ['people' => $people],
            'units' => ['tip_amount' => 'currency', 'total_with_tip' => 'currency', 'per_person' => 'currency'],
        ];
    }
}
