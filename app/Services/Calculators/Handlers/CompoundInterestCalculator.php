<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Compound interest calculator using A = P * (1 + r/n)^(n*t), where r is
 * the annual interest rate, n the compounding frequency per year and t
 * the time in years.
 */
class CompoundInterestCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'compound_interest_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('principal', 'Principal Amount', 'number', ['unit' => 'currency', 'min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000]),
            $this->field('annual_rate', 'Annual Interest Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 8]),
            $this->field('time_years', 'Time Period', 'number', ['unit' => 'years', 'min' => 0.01, 'max' => 100, 'step' => 0.01, 'default' => 5]),
            $this->field('compounding_frequency', 'Compounding Frequency', 'select', [
                'options' => ['1' => 'Annually', '2' => 'Semi-Annually', '4' => 'Quarterly', '12' => 'Monthly', '365' => 'Daily'],
                'default' => '1',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $principal = $this->requireNumeric($inputs, 'principal');
        $annualRate = $this->requireNumeric($inputs, 'annual_rate');
        $timeYears = $this->requireNumeric($inputs, 'time_years');
        $frequency = $this->toFloat($inputs, 'compounding_frequency', 1);
        $frequency = $frequency > 0 ? $frequency : 1;

        $rateFraction = $annualRate / 100;
        $amount = $principal * ((1 + $rateFraction / $frequency) ** ($frequency * $timeYears));
        $interest = $amount - $principal;

        return [
            'results' => [
                'maturity_amount' => $this->round($amount),
                'interest_earned' => $this->round($interest),
            ],
            'breakdown' => [
                'principal' => $this->round($principal),
                'compounding_frequency_per_year' => $frequency,
                'time_years' => $timeYears,
            ],
            'units' => [
                'maturity_amount' => 'currency',
                'interest_earned' => 'currency',
            ],
        ];
    }
}
