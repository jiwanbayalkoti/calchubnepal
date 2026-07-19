<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * IPO / FPO allotment probability estimator (Nepal CDS / issue manager
 * lottery-style when oversubscribed). Uses a simple proportional model.
 */
class IpoAllotmentCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ipo_allotment_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('total_kits', 'Total Kits / Units Available', 'number', [
                'min' => 1,
                'max' => 1000000000,
                'step' => 1,
                'default' => 1000000,
            ]),
            $this->field('total_applicants', 'Valid Applicants (or applications)', 'number', [
                'min' => 1,
                'max' => 1000000000,
                'step' => 1,
                'default' => 1500000,
            ]),
            $this->field('your_kits', 'Kits You Applied For', 'number', [
                'min' => 1,
                'max' => 1000000000,
                'step' => 1,
                'default' => 10,
            ]),
            $this->field('issue_price', 'Issue Price per Kit (optional)', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 100,
                'unit' => 'NPR',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $kits = $this->requireNumeric($inputs, 'total_kits');
        $applicants = $this->requireNumeric($inputs, 'total_applicants');
        $yours = $this->requireNumeric($inputs, 'your_kits');
        $price = $this->toFloat($inputs, 'issue_price', 0);

        if ($applicants <= 0 || $kits <= 0) {
            throw new InvalidArgumentException('Kits and applicants must be positive.');
        }

        $oversubscription = $applicants / $kits;
        $probability = min(100.0, $this->percentageOf($kits, $applicants));
        $expectedKits = ($kits / $applicants) * $yours;
        $amountBlocked = $yours * $price;
        $expectedAllotmentValue = $expectedKits * $price;

        return [
            'results' => [
                'oversubscription_times' => $this->round($oversubscription, 2),
                'allotment_probability_percent' => $this->round($probability, 2),
                'expected_kits' => $this->round($expectedKits, 4),
                'amount_applied_npr' => $this->round($amountBlocked),
                'expected_allotment_value_npr' => $this->round($expectedAllotmentValue),
            ],
            'breakdown' => [
                'model' => 'Proportional, lottery-style estimate when oversubscribed (probability ≈ kits ÷ applicants)',
                'note' => 'Actual Nepal IPO allotment uses CDS lottery rules / minimum kit guarantees that can differ from pure proportion.',
            ],
            'units' => [
                'oversubscription_times' => 'x',
                'allotment_probability_percent' => '%',
                'expected_kits' => 'kits',
                'amount_applied_npr' => 'NPR',
                'expected_allotment_value_npr' => 'NPR',
            ],
        ];
    }
}
