<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Gratuity estimator with Nepal Labor Act 2074 style contribution
 * (8.33% of basic per month ≈ 1 month basic per year) and the common
 * Indian Payment of Gratuity Act style (15/26 × monthly × years).
 */
class GratuityCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'gratuity_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('method', 'Calculation Method', 'select', [
                'options' => [
                    'nepal_labor_act' => 'Nepal Labor Act 2074 (8.33% / 1 month per year)',
                    'days_15_26' => '15 days × years ÷ 26 (common rule of thumb)',
                ],
                'default' => 'nepal_labor_act',
            ]),
            $this->field('last_salary', 'Monthly Basic Salary', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 50000,
                'unit' => 'NPR',
            ]),
            $this->field('years', 'Years of Service', 'number', [
                'min' => 0.5,
                'max' => 50,
                'step' => 0.01,
                'default' => 10,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $salary = $this->requireNumeric($inputs, 'last_salary');
        $years = $this->requireNumeric($inputs, 'years');
        $method = $this->toString($inputs, 'method', 'nepal_labor_act');

        if ($method === 'days_15_26') {
            $gratuity = ($salary / 26) * 15 * $years;
            $formula = '(Monthly basic ÷ 26) × 15 × years of service';
            $monthlyAccrual = ($salary / 26) * 15 / 12;
        } else {
            // Labor Act 2074: employer contributes 8.33% of basic monthly ≈ 1 month/year
            $gratuity = $salary * $years;
            $formula = 'Monthly basic × years (≈ 8.33% of basic accrued each month)';
            $monthlyAccrual = $salary * 0.0833;
        }

        return [
            'results' => [
                'estimated_gratuity' => $this->round($gratuity),
                'implied_monthly_accrual' => $this->round($monthlyAccrual),
            ],
            'breakdown' => [
                'method' => $method === 'days_15_26' ? '15/26 rule' : 'Nepal Labor Act 2074 style',
                'formula' => $formula,
                'years_of_service' => $years,
                'note' => 'Eligibility, caps and SSF/CIT integration vary — verify under your employment contract and applicable labor law.',
            ],
            'units' => [
                'estimated_gratuity' => 'NPR',
                'implied_monthly_accrual' => 'NPR',
            ],
        ];
    }
}
