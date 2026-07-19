<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Sales Tax Calculator
 */
class SalesTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'sales_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000, 'unit' => 'currency']),
            $this->field('tax_rate', 'Tax Rate', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 13, 'unit' => '%']),
            $this->field('mode', 'Mode', 'select', ['options' => ['exclusive' => 'Tax exclusive', 'inclusive' => 'Tax inclusive'], 'default' => 'exclusive']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'tax_rate');
        if ($this->toString($inputs, 'mode', 'exclusive') === 'inclusive') {
            $base = $amount / (1 + $rate / 100);
            $tax = $amount - $base;
            $total = $amount;
        } else {
            $base = $amount;
            $tax = $amount * $rate / 100;
            $total = $amount + $tax;
        }
        return [
            'results' => [
                'taxable_base' => $this->round($base),
                'tax_amount' => $this->round($tax),
                'total' => $this->round($total),
            ],
            'breakdown' => ['rate_percent' => $rate],
            'units' => ['taxable_base' => 'currency', 'tax_amount' => 'currency', 'total' => 'currency'],
        ];
    }
}
