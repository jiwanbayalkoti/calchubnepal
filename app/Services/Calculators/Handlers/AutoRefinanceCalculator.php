<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Auto Refinance Calculator
 * Current loan vs refinance offer: payment savings, fee breakeven, interest savings, verdict.
 */
class AutoRefinanceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'auto_refinance_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('current_balance', 'Current Loan Balance', 'number', ['min' => 1, 'max' => 500000, 'step' => 1, 'default' => 18000, 'unit' => 'currency']),
            $this->field('current_rate', 'Current APR', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 8.9, 'unit' => '%']),
            $this->field('current_remaining_months', 'Months Remaining (Current)', 'number', ['min' => 1, 'max' => 120, 'step' => 1, 'default' => 48, 'unit' => 'months']),
            $this->field('new_rate', 'New Refinance APR', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 5.9, 'unit' => '%']),
            $this->field('new_term_months', 'New Loan Term', 'number', ['min' => 1, 'max' => 120, 'step' => 1, 'default' => 48, 'unit' => 'months']),
            $this->field('refinance_fees', 'Refinance Fees / Costs', 'number', ['min' => 0, 'max' => 10000, 'step' => 1, 'default' => 400, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $balance = $this->requireNumeric($inputs, 'current_balance');
        $currentRate = $this->requireNumeric($inputs, 'current_rate');
        $currentMonths = (int) max(1, round($this->requireNumeric($inputs, 'current_remaining_months')));
        $newRate = $this->requireNumeric($inputs, 'new_rate');
        $newMonths = (int) max(1, round($this->requireNumeric($inputs, 'new_term_months')));
        $fees = max(0, $this->requireNumeric($inputs, 'refinance_fees'));

        $currentPayment = $this->emi($balance, $currentRate, $currentMonths);
        $newPayment = $this->emi($balance, $newRate, $newMonths);

        $currentTotalInterest = ($currentPayment * $currentMonths) - $balance;
        $newTotalInterest = ($newPayment * $newMonths) - $balance;
        $interestSavings = $currentTotalInterest - $newTotalInterest;
        $monthlySavings = $currentPayment - $newPayment;
        $netInterestSavings = $interestSavings - $fees;

        $breakevenMonths = null;
        if ($monthlySavings > 0.005) {
            $breakevenMonths = (int) ceil($fees / $monthlySavings);
        } elseif ($fees <= 0 && $monthlySavings >= 0) {
            $breakevenMonths = 0;
        }

        // Verdict score 0–100: interest net savings + payment relief, penalty for long payback.
        $score = 50.0;
        $score += min(30, max(-30, $netInterestSavings / max(1, $balance) * 200));
        $score += min(15, max(-15, $monthlySavings / max(1, $currentPayment) * 50));
        if ($breakevenMonths !== null) {
            if ($breakevenMonths <= 12) {
                $score += 10;
            } elseif ($breakevenMonths <= 24) {
                $score += 5;
            } elseif ($breakevenMonths > $newMonths) {
                $score -= 20;
            }
        } else {
            $score -= 25;
        }
        $score = max(0, min(100, $score));

        if ($score >= 70 && $netInterestSavings > 0 && $monthlySavings > 0) {
            $verdict = 'Strong yes — refinance pencils out';
        } elseif ($score >= 55 && $netInterestSavings > 0) {
            $verdict = 'Likely yes — modest but positive savings';
        } elseif ($netInterestSavings > 0 && $monthlySavings <= 0) {
            $verdict = 'Mixed — interest savings but higher/same payment (check cash flow)';
        } else {
            $verdict = 'Skip — fees and terms do not beat your current loan';
        }

        return [
            'results' => [
                'current_monthly_payment' => $this->round($currentPayment),
                'new_monthly_payment' => $this->round($newPayment),
                'monthly_payment_savings' => $this->round($monthlySavings),
                'breakeven_month_on_fees' => $breakevenMonths === null ? 'Never' : $breakevenMonths,
                'current_remaining_interest' => $this->round($currentTotalInterest),
                'new_total_interest' => $this->round($newTotalInterest),
                'gross_interest_savings' => $this->round($interestSavings),
                'net_savings_after_fees' => $this->round($netInterestSavings),
                'verdict_score' => $this->round($score, 0),
                'verdict' => $verdict,
            ],
            'breakdown' => [
                'refinance_fees' => $this->round($fees),
                'current_term_months' => $currentMonths,
                'new_term_months' => $newMonths,
                'formula' => 'EMI reducing-balance; net savings = interest saved − fees; breakeven = fees ÷ monthly payment savings',
            ],
            'units' => [
                'current_monthly_payment' => 'currency',
                'new_monthly_payment' => 'currency',
                'monthly_payment_savings' => 'currency',
                'breakeven_month_on_fees' => 'months',
                'current_remaining_interest' => 'currency',
                'new_total_interest' => 'currency',
                'gross_interest_savings' => 'currency',
                'net_savings_after_fees' => 'currency',
                'verdict_score' => '/100',
                'verdict' => '',
            ],
        ];
    }

    protected function emi(float $principal, float $annualRatePct, int $months): float
    {
        if ($months <= 0) {
            return 0.0;
        }

        $r = $annualRatePct / 12 / 100;
        if ($r == 0.0) {
            return $principal / $months;
        }

        $factor = (1 + $r) ** $months;

        return $principal * $r * $factor / ($factor - 1);
    }
}
