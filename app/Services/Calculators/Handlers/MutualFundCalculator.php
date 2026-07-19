<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Mutual Fund Calculator
 */
class MutualFundCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'mutual_fund_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_investment', 'Monthly Investment', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000, 'unit' => 'currency']),
            $this->field('expected_return', 'Expected Annual Return', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 12, 'unit' => '%']),
            $this->field('years', 'Years', 'number', ['min' => 0.5, 'max' => 50, 'step' => 0.01, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $sip = $this->requireNumeric($inputs, 'monthly_investment');
        $annual = $this->requireNumeric($inputs, 'expected_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        $fv = $r == 0.0 ? $sip * $months : $sip * (((1 + $r) ** $months - 1) / $r) * (1 + $r);
        $invested = $sip * $months;
        return [
            'results' => [
                'future_value' => $this->round($fv),
                'total_invested' => $this->round($invested),
                'estimated_gain' => $this->round($fv - $invested),
            ],
            'breakdown' => ['months' => $months, 'monthly_rate_percent' => $this->round($r * 100, 4)],
            'units' => ['future_value' => 'currency', 'total_invested' => 'currency', 'estimated_gain' => 'currency'],
        ];
    }
}
