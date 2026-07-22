<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * HSA Triple-Tax Optimizer
 * HSA growth vs Trad 401(k), Roth 401(k), taxable; shoebox receipt timeline.
 */
class HsaTripleTaxOptimizerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'hsa_triple_tax_optimizer_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('annual_contribution', 'Annual HSA Contribution', 'number', ['min' => 0, 'max' => 10000, 'step' => 50, 'default' => 4300, 'unit' => 'currency']),
            $this->field('years_to_retirement', 'Years to Retirement', 'number', ['min' => 1, 'max' => 50, 'step' => 1, 'default' => 25, 'unit' => 'years']),
            $this->field('expected_return', 'Expected Return', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 7, 'unit' => '%']),
            $this->field('current_marginal_rate', 'Current Marginal Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 32, 'unit' => '%']),
            $this->field('retirement_marginal_rate', 'Retirement Marginal Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 22, 'unit' => '%']),
            $this->field('state_rate', 'State Tax Rate (now)', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('medical_receipts_annual', 'Qualified Medical Receipts Banked / Yr', 'number', ['min' => 0, 'max' => 20000, 'step' => 50, 'default' => 2000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $contrib = $this->requireNumeric($inputs, 'annual_contribution');
        $n = (int) max(1, round($this->requireNumeric($inputs, 'years_to_retirement')));
        $r = $this->requireNumeric($inputs, 'expected_return') / 100;
        $tNow = ($this->requireNumeric($inputs, 'current_marginal_rate') + $this->toFloat($inputs, 'state_rate', 0)) / 100;
        $tRet = $this->requireNumeric($inputs, 'retirement_marginal_rate') / 100;
        $receiptsYr = $this->toFloat($inputs, 'medical_receipts_annual', 2000);

        $fv = $this->fvAnnuity($contrib, $r, $n);

        // HSA: deduct now, grow tax-free, withdraw medical tax-free (triple tax advantage)
        $hsaNet = $fv; // if used for medical / shoebox
        $hsaUpfrontBenefit = $contrib * $tNow * $n; // cumulative deduction value (undiscounted sum)

        // Trad 401k: deduct now, tax at withdrawal
        $tradNet = $fv * (1 - $tRet);

        // Roth 401k: no upfront deduction; tax-free later (contribute after-tax equivalent)
        $rothContrib = $contrib * (1 - $tNow);
        $rothFv = $this->fvAnnuity($rothContrib, $r, $n);
        $rothNet = $rothFv;

        // Taxable brokerage: no deduction; tax gains roughly at 15% LTCG on gains
        $taxableFv = $this->fvAnnuity($rothContrib, $r, $n);
        $taxableBasis = $rothContrib * $n;
        $taxableNet = $taxableFv - max(0, $taxableFv - $taxableBasis) * 0.15;

        $receiptsBanked = $receiptsYr * $n;
        $shoeboxYears = $receiptsYr > 0 ? (int) ceil($fv / max(1, $receiptsYr)) : $n;
        $optimal = 'Invest HSA, pay medical out-of-pocket, save receipts (shoebox) for tax-free withdrawal later';

        return [
            'results' => [
                'hsa_balance_at_retirement' => $this->round($fv),
                'hsa_net_if_medical_qualified' => $this->round($hsaNet),
                'trad_401k_net_after_tax' => $this->round($tradNet),
                'roth_401k_net' => $this->round($rothNet),
                'taxable_brokerage_net' => $this->round($taxableNet),
                'hsa_advantage_vs_trad' => $this->round($hsaNet - $tradNet),
                'receipts_banked_total' => $this->round($receiptsBanked),
                'shoebox_years_to_cover_balance' => $shoeboxYears,
                'optimal_strategy' => $optimal,
            ],
            'breakdown' => [
                'cumulative_upfront_tax_savings_est' => $this->round($hsaUpfrontBenefit),
                'formula' => 'HSA = deduct + tax-free growth + tax-free medical withdrawal; compare ending nets vs Trad/Roth/taxable',
            ],
            'units' => [
                'hsa_balance_at_retirement' => 'currency',
                'hsa_net_if_medical_qualified' => 'currency',
                'trad_401k_net_after_tax' => 'currency',
                'roth_401k_net' => 'currency',
                'taxable_brokerage_net' => 'currency',
                'hsa_advantage_vs_trad' => 'currency',
                'receipts_banked_total' => 'currency',
                'shoebox_years_to_cover_balance' => 'years',
                'optimal_strategy' => '',
            ],
        ];
    }

    protected function fvAnnuity(float $pmt, float $r, int $n): float
    {
        if ($r == 0.0) {
            return $pmt * $n;
        }

        return $pmt * (((1 + $r) ** $n - 1) / $r);
    }
}
