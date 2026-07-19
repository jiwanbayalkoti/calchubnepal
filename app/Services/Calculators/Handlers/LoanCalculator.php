<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * General-purpose loan repayment calculator using the standard reducing
 * balance EMI formula, extended with an optional one-time processing fee
 * to arrive at the true total cost of the loan.
 */
class LoanCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'loan_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('principal', 'Loan Principal', 'number', ['unit' => 'currency', 'min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 500000]),
            $this->field('annual_rate', 'Annual Interest Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 10]),
            $this->field('tenure_months', 'Tenure', 'number', ['unit' => 'months', 'min' => 1, 'max' => 600, 'step' => 1, 'default' => 36]),
            $this->field('processing_fee_percent', 'Processing Fee', 'number', ['unit' => '%', 'min' => 0, 'max' => 10, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $principal = $this->requireNumeric($inputs, 'principal');
        $annualRate = $this->requireNumeric($inputs, 'annual_rate');
        $months = $this->toInt($inputs, 'tenure_months', 12);
        $feePercent = $this->toFloat($inputs, 'processing_fee_percent', 0);

        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0.0) {
            $emi = $this->safeDivide($principal, $months);
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $emi = $principal * $monthlyRate * $factor / ($factor - 1);
        }

        $totalPayment = $emi * $months;
        $totalInterest = $totalPayment - $principal;
        $processingFeeAmount = $principal * $feePercent / 100;
        $totalCost = $totalPayment + $processingFeeAmount;

        return [
            'results' => [
                'monthly_installment' => $this->round($emi),
                'total_interest' => $this->round($totalInterest),
                'processing_fee_amount' => $this->round($processingFeeAmount),
                'total_cost' => $this->round($totalCost),
            ],
            'breakdown' => [
                'principal' => $this->round($principal),
                'tenure_months' => $months,
                'total_payment_before_fee' => $this->round($totalPayment),
            ],
            'units' => [
                'monthly_installment' => 'currency',
                'total_interest' => 'currency',
                'processing_fee_amount' => 'currency',
                'total_cost' => 'currency',
            ],
        ];
    }
}
