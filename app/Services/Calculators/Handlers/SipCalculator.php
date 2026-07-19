<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Systematic Investment Plan future value calculator using the standard
 * annuity-due future value formula:
 * FV = P * [((1 + r)^n - 1) / r] * (1 + r)
 * where r is the monthly rate of return and n the number of installments.
 */
class SipCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'sip_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_investment', 'Monthly Investment', 'number', ['unit' => 'currency', 'min' => 1, 'max' => 100000000, 'step' => 0.01, 'default' => 5000]),
            $this->field('expected_annual_return', 'Expected Annual Return', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 12]),
            $this->field('investment_period_years', 'Investment Period', 'number', ['unit' => 'years', 'min' => 1, 'max' => 60, 'step' => 1, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $monthlyInvestment = $this->requireNumeric($inputs, 'monthly_investment');
        $annualReturn = $this->requireNumeric($inputs, 'expected_annual_return');
        $years = $this->requireNumeric($inputs, 'investment_period_years');

        $months = (int) round($years * 12);
        $monthlyRate = $annualReturn / 12 / 100;

        if ($monthlyRate == 0.0) {
            $maturityValue = $monthlyInvestment * $months;
        } else {
            $maturityValue = $monthlyInvestment
                * ((((1 + $monthlyRate) ** $months) - 1) / $monthlyRate)
                * (1 + $monthlyRate);
        }

        $totalInvested = $monthlyInvestment * $months;
        $wealthGained = $maturityValue - $totalInvested;

        return [
            'results' => [
                'maturity_value' => $this->round($maturityValue),
                'total_invested' => $this->round($totalInvested),
                'wealth_gained' => $this->round($wealthGained),
            ],
            'breakdown' => [
                'tenure_months' => $months,
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
            ],
            'units' => [
                'maturity_value' => 'currency',
                'total_invested' => 'currency',
                'wealth_gained' => 'currency',
            ],
        ];
    }
}
