<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pension Calculator
 */
class PensionCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pension_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('corpus', 'Retirement Corpus', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000000, 'unit' => 'currency']),
            $this->field('annual_return', 'Expected Annual Return', 'number', ['min' => 0, 'max' => 20, 'step' => 0.01, 'default' => 6, 'unit' => '%']),
            $this->field('years', 'Pension Years', 'number', ['min' => 1, 'max' => 40, 'step' => 0.01, 'default' => 20]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $corpus = $this->requireNumeric($inputs, 'corpus');
        $annual = $this->requireNumeric($inputs, 'annual_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        // PMT for annuity
        $monthly = $r == 0.0
            ? $this->safeDivide($corpus, $months)
            : $corpus * $r / (1 - (1 + $r) ** (-$months));
        return [
            'results' => [
                'monthly_pension' => $this->round($monthly),
                'annual_pension' => $this->round($monthly * 12),
                'total_payout' => $this->round($monthly * $months),
            ],
            'breakdown' => ['months' => $months],
            'units' => ['monthly_pension' => 'currency', 'annual_pension' => 'currency', 'total_payout' => 'currency'],
        ];
    }
}
