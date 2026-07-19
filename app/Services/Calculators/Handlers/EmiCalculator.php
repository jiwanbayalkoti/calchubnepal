<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Standard reducing-balance EMI (Equated Monthly Installment) formula:
 * EMI = P * r * (1 + r)^n / ((1 + r)^n - 1)
 * where r is the monthly interest rate and n the number of installments.
 */
class EmiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'emi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('loan_amount', 'Loan Amount', 'number', ['unit' => 'currency', 'min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000000]),
            $this->field('annual_interest_rate', 'Annual Interest Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 8.5]),
            $this->field('tenure_years', 'Loan Tenure', 'number', ['unit' => 'years', 'min' => 0.1, 'max' => 50, 'step' => 0.1, 'default' => 20]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $principal = $this->requireNumeric($inputs, 'loan_amount');
        $annualRate = $this->requireNumeric($inputs, 'annual_interest_rate');
        $tenureYears = $this->requireNumeric($inputs, 'tenure_years');

        $months = (int) round($tenureYears * 12);
        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0.0) {
            $emi = $this->safeDivide($principal, $months);
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $emi = $principal * $monthlyRate * $factor / ($factor - 1);
        }

        $totalPayment = $emi * $months;
        $totalInterest = $totalPayment - $principal;

        return [
            'results' => [
                'monthly_emi' => $this->round($emi),
                'total_interest' => $this->round($totalInterest),
                'total_payment' => $this->round($totalPayment),
            ],
            'breakdown' => [
                'principal' => $this->round($principal),
                'tenure_months' => $months,
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
                'formula' => 'EMI = P × r × (1+r)^n ÷ ((1+r)^n − 1) (reducing balance)',
            ],
            'units' => [
                'monthly_emi' => 'currency',
                'total_interest' => 'currency',
                'total_payment' => 'currency',
            ],
        ];
    }
}
