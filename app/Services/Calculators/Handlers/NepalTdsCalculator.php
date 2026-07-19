<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Nepal TDS / withholding tax estimator using common FY 2082/83 rates
 * under Income Tax Act 2058 (Finance Act 2082 — rates largely unchanged).
 */
class NepalTdsCalculator extends AbstractCalculatorHandler
{
    /**
     * Preset payment types → default TDS %.
     *
     * @var array<string, array{label: string, rate: float}>
     */
    protected const PAYMENT_TYPES = [
        'contract_vat' => ['label' => 'Contract / supply (VAT-registered, ≥50k)', 'rate' => 1.5],
        'service_vat' => ['label' => 'Service fee (VAT invoice)', 'rate' => 1.5],
        'service_pan' => ['label' => 'Service / consultancy (PAN bill, no VAT)', 'rate' => 15.0],
        'rent' => ['label' => 'House / land rent (natural person)', 'rate' => 10.0],
        'vehicle_rent_vat' => ['label' => 'Vehicle hire (VAT-registered)', 'rate' => 1.5],
        'interest_bank_individual' => ['label' => 'Bank/FI interest (natural person)', 'rate' => 6.0],
        'interest_other' => ['label' => 'Other interest / entity interest', 'rate' => 15.0],
        'dividend' => ['label' => 'Dividend (resident company)', 'rate' => 5.0],
        'royalty_commission' => ['label' => 'Royalty / commission', 'rate' => 15.0],
        'non_resident_contract' => ['label' => 'Contract / agreement to non-resident', 'rate' => 5.0],
        'custom' => ['label' => 'Custom rate', 'rate' => 1.5],
    ];

    public function key(): string
    {
        return 'nepal_tds_calculator';
    }

    public function inputSchema(): array
    {
        $options = [];
        foreach (self::PAYMENT_TYPES as $key => $meta) {
            $options[$key] = $meta['label'].' ('.$meta['rate'].'%)';
        }

        return [
            $this->field('payment', 'Payment Amount', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 100000,
                'unit' => 'NPR',
            ]),
            $this->field('payment_type', 'Payment Type', 'select', [
                'options' => $options,
                'default' => 'contract_vat',
            ]),
            $this->field('custom_tds_rate', 'Custom TDS Rate (if Custom)', 'number', [
                'min' => 0,
                'max' => 40,
                'step' => 0.01,
                'default' => 1.5,
                'unit' => '%',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $payment = $this->requireNumeric($inputs, 'payment');
        $type = $this->toString($inputs, 'payment_type', 'contract_vat');

        if (! isset(self::PAYMENT_TYPES[$type])) {
            throw new InvalidArgumentException('Invalid payment type.');
        }

        $rate = $type === 'custom'
            ? $this->toFloat($inputs, 'custom_tds_rate', self::PAYMENT_TYPES['custom']['rate'])
            : self::PAYMENT_TYPES[$type]['rate'];

        $tds = $payment * $rate / 100;

        return [
            'results' => [
                'tds_rate_percent' => $this->round($rate, 2),
                'tds_amount' => $this->round($tds),
                'net_payable' => $this->round($payment - $tds),
            ],
            'breakdown' => [
                'fiscal_year' => 'FY 2082/83 (indicative)',
                'payment_type' => self::PAYMENT_TYPES[$type]['label'],
                'gross_payment' => $this->round($payment),
                'note' => 'Common IRD withholding presets — confirm section 88/89 applicability, thresholds and DTAA relief for your case.',
            ],
            'units' => [
                'tds_rate_percent' => '%',
                'tds_amount' => 'NPR',
                'net_payable' => 'NPR',
            ],
        ];
    }
}
