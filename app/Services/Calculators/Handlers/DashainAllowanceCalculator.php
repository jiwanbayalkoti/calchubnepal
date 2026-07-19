<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Dashain (festival) allowance estimator.
 * Many Nepal employers pay ~1 month basic; Labor Act / org policy may vary.
 * Up to one month's basic festival bonus is commonly treated as tax-exempt
 * under income-tax practice — confirm current IRD treatment for your case.
 */
class DashainAllowanceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'dashain_allowance_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('basic_salary', 'Monthly Basic Salary', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 30000,
                'unit' => 'NPR',
            ]),
            $this->field('months', 'Allowance Months', 'number', [
                'min' => 0.5,
                'max' => 2,
                'step' => 0.5,
                'default' => 1,
            ]),
            $this->field('include_tax_note', 'Show tax-exempt note (≤1 month basic)', 'boolean', [
                'default' => true,
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $basic = $this->requireNumeric($inputs, 'basic_salary');
        $months = $this->requireNumeric($inputs, 'months');
        $allowance = $basic * $months;
        $exemptPortion = min($allowance, $basic); // typical ≤ 1 month basic
        $taxablePortion = max(0.0, $allowance - $exemptPortion);
        $showTax = $this->toBool($inputs, 'include_tax_note', true);

        $breakdown = [
            'basic_salary' => $this->round($basic),
            'allowance_months' => $months,
            'note' => 'Often 1 month basic — confirm your organization policy / CBA.',
        ];

        if ($showTax) {
            $breakdown['indicative_exempt_portion'] = $this->round($exemptPortion);
            $breakdown['indicative_taxable_portion'] = $this->round($taxablePortion);
            $breakdown['tax_note'] = 'Common practice: festival bonus up to 1 month basic may be exempt; amounts above may be taxable. Verify FY 2082/83 IRD treatment.';
        }

        return [
            'results' => [
                'dashain_allowance' => $this->round($allowance),
            ],
            'breakdown' => $breakdown,
            'units' => [
                'dashain_allowance' => 'NPR',
            ],
        ];
    }
}
