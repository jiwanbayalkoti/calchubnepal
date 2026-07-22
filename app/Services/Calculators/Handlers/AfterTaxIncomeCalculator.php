<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * After-Tax Income Calculator
 * Federal + state + FICA + Medicare with pre-tax levers and pay-period breakdowns.
 */
class AfterTaxIncomeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'after_tax_income_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('gross_income', 'Gross Annual Income', 'number', ['min' => 0, 'max' => 10000000, 'step' => 100, 'default' => 100000, 'unit' => 'currency']),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => ['single' => 'Single', 'mfj' => 'Married filing jointly', 'hoh' => 'Head of household'],
                'default' => 'single',
            ]),
            $this->field('state_rate', 'State Effective / Marginal Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('k401_contribution', '401(k) Pre-Tax Contribution', 'number', ['min' => 0, 'max' => 35000, 'step' => 100, 'default' => 10000, 'unit' => 'currency', 'required' => false]),
            $this->field('hsa_contribution', 'HSA Pre-Tax Contribution', 'number', ['min' => 0, 'max' => 10000, 'step' => 50, 'default' => 4300, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $gross = $this->requireNumeric($inputs, 'gross_income');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $stateRate = $this->toFloat($inputs, 'state_rate', 5) / 100;
        $k401 = $this->toFloat($inputs, 'k401_contribution', 0);
        $hsa = $this->toFloat($inputs, 'hsa_contribution', 0);
        $pretax = min($gross, $k401 + $hsa);

        $std = match ($status) {
            'mfj' => 30000.0,
            'hoh' => 22500.0,
            default => 15000.0,
        };
        $taxable = max(0, $gross - $pretax - $std);
        $federal = $this->progressive($taxable, $status);

        $ssWageBase = 176100.0; // illustrative
        $ss = min($gross, $ssWageBase) * 0.062;
        $medicare = $gross * 0.0145;
        $addlMed = 0.0;
        $addlThresh = $status === 'mfj' ? 250000.0 : 200000.0;
        if ($gross > $addlThresh) {
            $addlMed = ($gross - $addlThresh) * 0.009;
        }
        $fica = $ss + $medicare + $addlMed;
        $state = max(0, $gross - $pretax) * $stateRate;

        $afterTax = $gross - $federal - $state - $fica;
        // Note: 401k/HSA still "yours" but deferred — take-home excludes them
        $takeHome = $afterTax - $pretax;

        return [
            'results' => [
                'annual_after_tax_take_home' => $this->round($takeHome),
                'monthly' => $this->round($takeHome / 12),
                'bi_weekly' => $this->round($takeHome / 26),
                'weekly' => $this->round($takeHome / 52),
                'federal_income_tax' => $this->round($federal),
                'state_tax' => $this->round($state),
                'fica_medicare' => $this->round($fica),
                'pretax_deductions' => $this->round($pretax),
                'effective_tax_rate_pct' => $this->round((($federal + $state + $fica) / max(1, $gross)) * 100, 1),
            ],
            'breakdown' => [
                'taxable_income' => $this->round($taxable),
                'standard_deduction' => $std,
                'formula' => 'Take-home = gross − federal − state − FICA − pre-tax deferrals',
            ],
            'units' => [
                'annual_after_tax_take_home' => 'currency',
                'monthly' => 'currency',
                'bi_weekly' => 'currency',
                'weekly' => 'currency',
                'federal_income_tax' => 'currency',
                'state_tax' => 'currency',
                'fica_medicare' => 'currency',
                'pretax_deductions' => 'currency',
                'effective_tax_rate_pct' => '%',
            ],
        ];
    }

    protected function progressive(float $taxable, string $status): float
    {
        $brackets = $status === 'mfj'
            ? [[23850, 0.10], [96950, 0.12], [206700, 0.22], [394600, 0.24], [501050, 0.32], [751600, 0.35], [INF, 0.37]]
            : [[11925, 0.10], [48475, 0.12], [103350, 0.22], [197300, 0.24], [250525, 0.32], [626350, 0.35], [INF, 0.37]];
        $tax = 0.0;
        $prev = 0.0;
        foreach ($brackets as [$cap, $rate]) {
            $slice = min($taxable, $cap) - $prev;
            if ($slice > 0) {
                $tax += $slice * $rate;
            }
            if ($taxable <= $cap) {
                break;
            }
            $prev = $cap;
        }

        return $tax;
    }
}
