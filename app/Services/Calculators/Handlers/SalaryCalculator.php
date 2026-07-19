<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Salary Calculator
 */
class SalaryCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'salary_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('gross_monthly', 'Gross Monthly Salary', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50000, 'unit' => 'currency']),
            $this->field('deductions', 'Monthly Deductions', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000, 'unit' => 'currency']),
            $this->field('bonus_months', 'Bonus Months / Year', 'number', ['min' => 0, 'max' => 12, 'step' => 0.5, 'default' => 1]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $gross = $this->requireNumeric($inputs, 'gross_monthly');
        $ded = $this->requireNumeric($inputs, 'deductions');
        $bonus = $this->requireNumeric($inputs, 'bonus_months');
        $net = $gross - $ded;
        $annualCtc = $gross * (12 + $bonus);
        $bonusAmount = $gross * $bonus;

        return [
            'results' => [
                'net_monthly' => $this->round($net),
                'gross_annual' => $this->round($gross * 12),
                'annual_ctc' => $this->round($annualCtc),
                'annual_net_approx' => $this->round($net * 12 + $bonusAmount),
            ],
            'breakdown' => [
                'gross_monthly' => $this->round($gross),
                'deductions_monthly' => $this->round($ded),
                'bonus_amount' => $this->round($bonusAmount),
                'note' => 'For Nepal progressive tax on salary, use Nepal Income Tax or Salary Tax calculators.',
            ],
            'units' => [
                'net_monthly' => 'currency',
                'gross_annual' => 'currency',
                'annual_ctc' => 'currency',
                'annual_net_approx' => 'currency',
            ],
        ];
    }
}
