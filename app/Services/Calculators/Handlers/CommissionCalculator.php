<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Sales commission calculator: commission = sales_amount * rate / 100,
 * optionally added to a fixed base salary to produce total earnings.
 */
class CommissionCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'commission_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('sales_amount', 'Sales Amount', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000]),
            $this->field('commission_rate', 'Commission Rate', 'number', ['unit' => '%', 'min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 5]),
            $this->field('base_salary', 'Base Salary', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $salesAmount = $this->requireNumeric($inputs, 'sales_amount');
        $commissionRate = $this->requireNumeric($inputs, 'commission_rate');
        $baseSalary = $this->toFloat($inputs, 'base_salary', 0);

        $commission = $salesAmount * $commissionRate / 100;
        $totalEarnings = $baseSalary + $commission;

        return [
            'results' => [
                'commission_earned' => $this->round($commission),
                'total_earnings' => $this->round($totalEarnings),
            ],
            'breakdown' => [
                'sales_amount' => $this->round($salesAmount),
                'commission_rate' => $commissionRate,
                'base_salary' => $this->round($baseSalary),
            ],
            'units' => [
                'commission_earned' => 'currency',
                'total_earnings' => 'currency',
            ],
        ];
    }
}
