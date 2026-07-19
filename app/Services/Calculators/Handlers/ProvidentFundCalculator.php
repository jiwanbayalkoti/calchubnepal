<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Provident Fund / SSF contribution corpus estimator.
 * Nepal defaults: PF 10%+10% of basic, or SSF 11% employee + 20% employer
 * (scheme choice depends on organization enrollment).
 */
class ProvidentFundCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'provident_fund_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('scheme', 'Scheme Preset', 'select', [
                'options' => [
                    'nepal_pf' => 'Nepal PF (10% employee + 10% employer)',
                    'nepal_ssf' => 'Nepal SSF (11% employee + 20% employer)',
                    'custom' => 'Custom rates',
                ],
                'default' => 'nepal_pf',
            ]),
            $this->field('basic_salary', 'Monthly Basic Salary', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 40000,
                'unit' => 'NPR',
            ]),
            $this->field('employee_rate', 'Employee Contribution % (custom)', 'number', [
                'min' => 0,
                'max' => 30,
                'step' => 0.01,
                'default' => 10,
                'unit' => '%',
                'required' => false,
            ]),
            $this->field('employer_rate', 'Employer Contribution % (custom)', 'number', [
                'min' => 0,
                'max' => 30,
                'step' => 0.01,
                'default' => 10,
                'unit' => '%',
                'required' => false,
            ]),
            $this->field('years', 'Years', 'number', [
                'min' => 1,
                'max' => 40,
                'step' => 0.01,
                'default' => 10,
            ]),
            $this->field('annual_interest', 'Assumed Annual Interest / Return %', 'number', [
                'min' => 0,
                'max' => 15,
                'step' => 0.01,
                'default' => 6,
                'unit' => '%',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $basic = $this->requireNumeric($inputs, 'basic_salary');
        $scheme = $this->toString($inputs, 'scheme', 'nepal_pf');

        [$employeeRate, $employerRate, $schemeLabel] = match ($scheme) {
            'nepal_ssf' => [11.0, 20.0, 'Nepal SSF (11% + 20%)'],
            'custom' => [
                $this->toFloat($inputs, 'employee_rate', 10),
                $this->toFloat($inputs, 'employer_rate', 10),
                'Custom rates',
            ],
            default => [10.0, 10.0, 'Nepal PF (10% + 10%)'],
        };

        $employeeMonthly = $basic * $employeeRate / 100;
        $employerMonthly = $basic * $employerRate / 100;
        $monthly = $employeeMonthly + $employerMonthly;
        $months = (int) round($this->requireNumeric($inputs, 'years') * 12);
        $r = $this->requireNumeric($inputs, 'annual_interest') / 12 / 100;
        $fv = $r == 0.0
            ? $monthly * $months
            : $monthly * (((1 + $r) ** $months - 1) / $r) * (1 + $r);

        return [
            'results' => [
                'employee_monthly' => $this->round($employeeMonthly),
                'employer_monthly' => $this->round($employerMonthly),
                'monthly_contribution' => $this->round($monthly),
                'total_contributed' => $this->round($monthly * $months),
                'estimated_corpus' => $this->round($fv),
            ],
            'breakdown' => [
                'scheme' => $schemeLabel,
                'employee_rate_percent' => $employeeRate,
                'employer_rate_percent' => $employerRate,
                'months' => $months,
                'note' => 'SSF covers social security benefits beyond a pure PF corpus; interest/return is an assumption, not a guaranteed CIT/SSF rate.',
            ],
            'units' => [
                'employee_monthly' => 'NPR',
                'employer_monthly' => 'NPR',
                'monthly_contribution' => 'NPR',
                'total_contributed' => 'NPR',
                'estimated_corpus' => 'NPR',
            ],
        ];
    }
}
