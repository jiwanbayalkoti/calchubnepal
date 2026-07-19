<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Value Added Tax calculator supporting both exclusive (tax added on top)
 * and inclusive (tax already contained in the amount) calculation modes.
 */
class VatCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'vat_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Amount', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000]),
            $this->field('vat_rate', 'VAT Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 13]),
            $this->field('calculation_type', 'Calculation Type', 'select', [
                'options' => ['exclusive' => 'Add VAT (Exclusive)', 'inclusive' => 'Remove VAT (Inclusive)'],
                'default' => 'exclusive',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'vat_rate');
        $type = $this->toString($inputs, 'calculation_type', 'exclusive');

        if ($type === 'inclusive') {
            $baseAmount = $this->safeDivide($amount, 1 + $rate / 100);
            $vatAmount = $amount - $baseAmount;
            $totalAmount = $amount;
        } else {
            $baseAmount = $amount;
            $vatAmount = $amount * $rate / 100;
            $totalAmount = $amount + $vatAmount;
        }

        return [
            'results' => [
                'base_amount' => $this->round($baseAmount),
                'vat_amount' => $this->round($vatAmount),
                'total_amount' => $this->round($totalAmount),
            ],
            'breakdown' => [
                'vat_rate' => $rate,
                'calculation_type' => $type,
                'note' => 'Default 13% matches Nepal’s standard VAT; change the rate for other jurisdictions (e.g. 20% EU).',
            ],
            'units' => [
                'base_amount' => 'currency',
                'vat_amount' => 'currency',
                'total_amount' => 'currency',
            ],
        ];
    }
}
