<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Invoice Calculator
 */
class InvoiceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'invoice_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('subtotal', 'Subtotal', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000, 'unit' => 'currency']),
            $this->field('tax_percent', 'Tax %', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 13, 'unit' => '%']),
            $this->field('discount_percent', 'Discount %', 'number', ['min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 0, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $sub = $this->requireNumeric($inputs, 'subtotal');
        $discount = $sub * $this->toFloat($inputs, 'discount_percent') / 100;
        $taxable = $sub - $discount;
        $tax = $taxable * $this->requireNumeric($inputs, 'tax_percent') / 100;
        return [
            'results' => [
                'discount_amount' => $this->round($discount),
                'tax_amount' => $this->round($tax),
                'grand_total' => $this->round($taxable + $tax),
            ],
            'breakdown' => ['taxable_amount' => $this->round($taxable)],
            'units' => ['discount_amount' => 'currency', 'tax_amount' => 'currency', 'grand_total' => 'currency'],
        ];
    }
}
