<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * FD Calculator
 */
class FdCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'fd_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('principal', 'Deposit Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
            $this->field('rate', 'Annual Interest Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 7.5, 'unit' => '%']),
            $this->field('years', 'Tenure (Years)', 'number', ['min' => 0.25, 'max' => 20, 'step' => 0.01, 'default' => 3]),
            $this->field('compounding', 'Compounding', 'select', ['options' => ['yearly' => 'Yearly', 'half_yearly' => 'Half-Yearly', 'quarterly' => 'Quarterly', 'monthly' => 'Monthly'], 'default' => 'quarterly']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $p = $this->requireNumeric($inputs, 'principal');
        $rate = $this->requireNumeric($inputs, 'rate');
        $years = $this->requireNumeric($inputs, 'years');
        $n = match ($this->toString($inputs, 'compounding', 'quarterly')) {
            'yearly' => 1, 'half_yearly' => 2, 'monthly' => 12, default => 4,
        };
        $amount = $p * ((1 + ($rate / 100) / $n) ** ($n * $years));
        return [
            'results' => ['maturity_amount' => $this->round($amount), 'interest_earned' => $this->round($amount - $p)],
            'breakdown' => ['compounding_per_year' => $n],
            'units' => ['maturity_amount' => 'currency', 'interest_earned' => 'currency'],
        ];
    }
}
