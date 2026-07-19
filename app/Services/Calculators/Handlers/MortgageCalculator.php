<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Home mortgage payment calculator: computes principal & interest via the
 * standard amortization formula, then adds pro-rated monthly property tax
 * and insurance to arrive at the total monthly payment (PITI).
 */
class MortgageCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'mortgage_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('home_price', 'Home Price', 'number', ['unit' => 'currency', 'min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 300000]),
            $this->field('down_payment', 'Down Payment', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 60000]),
            $this->field('annual_interest_rate', 'Annual Interest Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 30, 'step' => 0.01, 'default' => 6.5]),
            $this->field('tenure_years', 'Loan Term', 'number', ['unit' => 'years', 'min' => 1, 'max' => 40, 'step' => 1, 'default' => 30]),
            $this->field('property_tax_annual', 'Annual Property Tax', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('insurance_annual', 'Annual Home Insurance', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $homePrice = $this->requireNumeric($inputs, 'home_price');
        $downPayment = $this->toFloat($inputs, 'down_payment', 0);
        $annualRate = $this->requireNumeric($inputs, 'annual_interest_rate');
        $tenureYears = $this->requireNumeric($inputs, 'tenure_years');
        $propertyTaxAnnual = $this->toFloat($inputs, 'property_tax_annual', 0);
        $insuranceAnnual = $this->toFloat($inputs, 'insurance_annual', 0);

        $loanAmount = max(0, $homePrice - $downPayment);
        $months = (int) round($tenureYears * 12);
        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0.0) {
            $principalAndInterest = $this->safeDivide($loanAmount, $months);
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $principalAndInterest = $loanAmount * $monthlyRate * $factor / ($factor - 1);
        }

        $monthlyTax = $propertyTaxAnnual / 12;
        $monthlyInsurance = $insuranceAnnual / 12;
        $totalMonthlyPayment = $principalAndInterest + $monthlyTax + $monthlyInsurance;

        $totalPayment = $principalAndInterest * $months;
        $totalInterest = $totalPayment - $loanAmount;

        return [
            'results' => [
                'loan_amount' => $this->round($loanAmount),
                'principal_and_interest' => $this->round($principalAndInterest),
                'total_monthly_payment' => $this->round($totalMonthlyPayment),
                'total_interest' => $this->round($totalInterest),
            ],
            'breakdown' => [
                'monthly_tax' => $this->round($monthlyTax),
                'monthly_insurance' => $this->round($monthlyInsurance),
                'tenure_months' => $months,
            ],
            'units' => [
                'loan_amount' => 'currency',
                'principal_and_interest' => 'currency',
                'total_monthly_payment' => 'currency',
                'total_interest' => 'currency',
            ],
        ];
    }
}
