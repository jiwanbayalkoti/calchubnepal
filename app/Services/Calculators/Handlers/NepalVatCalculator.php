<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Nepal VAT calculator. Standard rate remains 13% under the VAT Act
 * (FY 2082/83 — no rate change in Finance Act 2082 for the standard band).
 */
class NepalVatCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'nepal_vat_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Amount', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 10000,
                'unit' => 'NPR',
            ]),
            $this->field('vat_rate', 'VAT Rate', 'select', [
                'options' => [
                    '13' => '13% (standard)',
                    '0' => '0% (zero-rated / exempt estimate)',
                    'custom' => 'Custom %',
                ],
                'default' => '13',
            ]),
            $this->field('custom_vat_rate', 'Custom VAT Rate', 'number', [
                'min' => 0,
                'max' => 30,
                'step' => 0.01,
                'default' => 13,
                'unit' => '%',
                'required' => false,
            ]),
            $this->field('mode', 'Mode', 'select', [
                'options' => [
                    'exclusive' => 'Add VAT (exclusive)',
                    'inclusive' => 'Extract VAT (inclusive)',
                ],
                'default' => 'exclusive',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $rateKey = $this->toString($inputs, 'vat_rate', '13');
        $rate = $rateKey === 'custom'
            ? $this->toFloat($inputs, 'custom_vat_rate', 13)
            : (float) $rateKey;

        if ($this->toString($inputs, 'mode', 'exclusive') === 'inclusive') {
            $base = $this->safeDivide($amount, 1 + $rate / 100);
            $vat = $amount - $base;
            $total = $amount;
        } else {
            $base = $amount;
            $vat = $amount * $rate / 100;
            $total = $amount + $vat;
        }

        return [
            'results' => [
                'taxable_amount' => $this->round($base),
                'vat_amount' => $this->round($vat),
                'total_amount' => $this->round($total),
            ],
            'breakdown' => [
                'fiscal_year' => 'FY 2082/83',
                'vat_rate_percent' => $this->round($rate, 2),
                'mode' => $this->toString($inputs, 'mode', 'exclusive'),
                'note' => 'Nepal standard VAT is 13%. Zero-rated/exempt supplies need invoice classification — this tool only does arithmetic.',
            ],
            'units' => [
                'taxable_amount' => 'NPR',
                'vat_amount' => 'NPR',
                'total_amount' => 'NPR',
            ],
        ];
    }
}
