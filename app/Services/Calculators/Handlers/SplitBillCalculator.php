<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Split Bill Calculator
 */
class SplitBillCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'split_bill_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('total', 'Total Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 3000, 'unit' => 'currency']),
            $this->field('people', 'Number of People', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 4]),
            $this->field('tip_percent', 'Tip % (optional)', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 0, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $total = $this->requireNumeric($inputs, 'total');
        $tip = $total * $this->toFloat($inputs, 'tip_percent') / 100;
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $grand = $total + $tip;
        return [
            'results' => [
                'grand_total' => $this->round($grand),
                'per_person' => $this->round($grand / $people),
            ],
            'breakdown' => ['tip_amount' => $this->round($tip)],
            'units' => ['grand_total' => 'currency', 'per_person' => 'currency'],
        ];
    }
}
