<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Goods and Services Tax calculator supporting both exclusive (tax added
 * on top of the amount) and inclusive (tax already baked into the amount)
 * calculation modes.
 */
class GstCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'gst_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Amount', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000]),
            $this->field('gst_rate', 'GST Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 18]),
            $this->field('calculation_type', 'Calculation Type', 'select', [
                'options' => ['exclusive' => 'Add GST (Exclusive)', 'inclusive' => 'Remove GST (Inclusive)'],
                'default' => 'exclusive',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'gst_rate');
        $type = $this->toString($inputs, 'calculation_type', 'exclusive');

        if ($type === 'inclusive') {
            $baseAmount = $this->safeDivide($amount, 1 + $rate / 100);
            $gstAmount = $amount - $baseAmount;
            $totalAmount = $amount;
        } else {
            $baseAmount = $amount;
            $gstAmount = $amount * $rate / 100;
            $totalAmount = $amount + $gstAmount;
        }

        return [
            'results' => [
                'base_amount' => $this->round($baseAmount),
                'gst_amount' => $this->round($gstAmount),
                'total_amount' => $this->round($totalAmount),
            ],
            'breakdown' => [
                'gst_rate' => $rate,
                'calculation_type' => $type,
            ],
            'units' => [
                'base_amount' => 'currency',
                'gst_amount' => 'currency',
                'total_amount' => 'currency',
            ],
        ];
    }
}
