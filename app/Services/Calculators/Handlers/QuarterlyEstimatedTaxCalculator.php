<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Quarterly Estimated Tax Calculator
 * IRS safe harbor — what to send by April 15 and each quarter.
 */
class QuarterlyEstimatedTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'quarterly_estimated_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('projected_agi', 'Projected AGI This Year', 'number', ['min' => 0, 'max' => 10000000, 'step' => 100, 'default' => 180000, 'unit' => 'currency']),
            $this->field('projected_federal_tax', 'Projected Federal Tax Liability', 'number', ['min' => 0, 'max' => 3000000, 'step' => 100, 'default' => 32000, 'unit' => 'currency']),
            $this->field('withholding_ytd_and_expected', 'Expected Federal Withholding This Year', 'number', ['min' => 0, 'max' => 3000000, 'step' => 100, 'default' => 12000, 'unit' => 'currency']),
            $this->field('last_year_total_tax', "Last Year's Total Tax (Form 1040)", 'number', ['min' => 0, 'max' => 3000000, 'step' => 100, 'default' => 28000, 'unit' => 'currency']),
            $this->field('last_year_agi', "Last Year's AGI", 'number', ['min' => 0, 'max' => 10000000, 'step' => 100, 'default' => 150000, 'unit' => 'currency']),
            $this->field('credits', 'Expected Credits', 'number', ['min' => 0, 'max' => 100000, 'step' => 50, 'default' => 0, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $agi = $this->requireNumeric($inputs, 'projected_agi');
        $tax = max(0, $this->requireNumeric($inputs, 'projected_federal_tax') - $this->toFloat($inputs, 'credits', 0));
        $withholding = $this->requireNumeric($inputs, 'withholding_ytd_and_expected');
        $lastTax = $this->requireNumeric($inputs, 'last_year_total_tax');
        $lastAgi = $this->requireNumeric($inputs, 'last_year_agi');

        $highIncome = $lastAgi > 150000; // MFJ threshold simplified — uses $150k single-ish; note MFJ is $150k
        $safeHarborPct = $highIncome ? 0.110 : 1.00; // 110% if AGI > $150k prior year
        // Actually: safe harbor is 100% of prior year tax, or 110% if AGI > 150k (MFJ)
        $safeHarborAmount = $lastTax * ($highIncome ? 1.10 : 1.00);
        $ninetyCurrent = $tax * 0.90;

        $requiredAnnual = min($safeHarborAmount, $ninetyCurrent);
        // If current year tax is lower, required is based on current; safe harbor is the lesser of the two tests' required payments
        // Required estimated = requiredAnnual - withholding
        $requiredPayments = max(0, $requiredAnnual - $withholding);
        $perQuarter = $requiredPayments / 4;

        $remainingTax = max(0, $tax - $withholding);

        return [
            'results' => [
                'safe_harbor_annual_target' => $this->round($safeHarborAmount),
                'ninety_pct_current_year_target' => $this->round($ninetyCurrent),
                'required_annual_to_avoid_penalty' => $this->round($requiredAnnual),
                'total_estimated_payments_needed' => $this->round($requiredPayments),
                'payment_each_quarter' => $this->round($perQuarter),
                'april_15_payment' => $this->round($perQuarter),
                'high_income_110_pct_rule' => $highIncome ? 'Yes — using 110% of last year\'s tax' : 'No — using 100% of last year\'s tax',
                'note' => 'Send the same quarterly amount for Q1–Q4 under annualized-safe-harbor equal installments (Form 1040-ES).',
            ],
            'breakdown' => [
                'projected_tax_after_credits' => $this->round($tax),
                'expected_withholding' => $this->round($withholding),
                'remaining_tax_if_no_estimates' => $this->round($remainingTax),
                'formula' => 'Required = min(90% current-year tax, 100%/110% prior-year tax) − withholding; ÷ 4 for each quarter',
            ],
            'units' => [
                'safe_harbor_annual_target' => 'currency',
                'ninety_pct_current_year_target' => 'currency',
                'required_annual_to_avoid_penalty' => 'currency',
                'total_estimated_payments_needed' => 'currency',
                'payment_each_quarter' => 'currency',
                'april_15_payment' => 'currency',
                'high_income_110_pct_rule' => '',
                'note' => '',
            ],
        ];
    }
}
