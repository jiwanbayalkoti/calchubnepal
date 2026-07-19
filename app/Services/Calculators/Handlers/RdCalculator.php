<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * RD Calculator
 */
class RdCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rd_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_deposit', 'Monthly Deposit', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000, 'unit' => 'currency']),
            $this->field('rate', 'Annual Interest Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 6.5, 'unit' => '%']),
            $this->field('months', 'Tenure (Months)', 'number', ['min' => 1, 'max' => 120, 'step' => 1, 'default' => 24]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $p = $this->requireNumeric($inputs, 'monthly_deposit');
        $rate = $this->requireNumeric($inputs, 'rate');
        $n = (int) $this->requireNumeric($inputs, 'months');
        $r = $rate / 400; // quarterly compounding approximation used by many banks
        $maturity = $p * (((1 + $r) ** ($n / 3) - 1) / (1 - (1 + $r) ** (-1 / 3)));
        $invested = $p * $n;
        return [
            'results' => [
                'maturity_amount' => $this->round($maturity),
                'total_deposited' => $this->round($invested),
                'interest_earned' => $this->round($maturity - $invested),
            ],
            'breakdown' => ['months' => $n],
            'units' => ['maturity_amount' => 'currency', 'total_deposited' => 'currency', 'interest_earned' => 'currency'],
        ];
    }
}
