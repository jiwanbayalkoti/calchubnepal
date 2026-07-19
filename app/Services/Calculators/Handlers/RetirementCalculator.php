<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Retirement Calculator
 */
class RetirementCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'retirement_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('current_savings', 'Current Savings', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 500000, 'unit' => 'currency']),
            $this->field('monthly_contribution', 'Monthly Contribution', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000, 'unit' => 'currency']),
            $this->field('annual_return', 'Expected Annual Return', 'number', ['min' => 0, 'max' => 30, 'step' => 0.01, 'default' => 10, 'unit' => '%']),
            $this->field('years', 'Years Until Retirement', 'number', ['min' => 1, 'max' => 50, 'step' => 0.01, 'default' => 25]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $pv = $this->requireNumeric($inputs, 'current_savings');
        $pmt = $this->requireNumeric($inputs, 'monthly_contribution');
        $annual = $this->requireNumeric($inputs, 'annual_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        $fvLump = $pv * ((1 + $r) ** $months);
        $fvSip = $r == 0.0 ? $pmt * $months : $pmt * (((1 + $r) ** $months - 1) / $r) * (1 + $r);
        $total = $fvLump + $fvSip;
        return [
            'results' => [
                'retirement_corpus' => $this->round($total),
                'from_current_savings' => $this->round($fvLump),
                'from_contributions' => $this->round($fvSip),
            ],
            'breakdown' => ['months' => $months],
            'units' => ['retirement_corpus' => 'currency', 'from_current_savings' => 'currency', 'from_contributions' => 'currency'],
        ];
    }
}
