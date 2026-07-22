<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Mega-Backdoor Roth Calculator
 * Eligibility, after-tax headroom, lifetime Roth growth vs taxable.
 */
class MegaBackdoorRothCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'mega_backdoor_roth_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('plan_allows_after_tax', 'Plan Allows After-Tax Contributions?', 'select', [
                'options' => ['yes' => 'Yes', 'no' => 'No / unknown'],
                'default' => 'yes',
            ]),
            $this->field('in_plan_conversion', 'In-Plan Roth Conversion / In-Service Withdrawal?', 'select', [
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes',
            ]),
            $this->field('employee_deferral', 'Your 401(k) Deferrals YTD (pre-tax+Roth)', 'number', ['min' => 0, 'max' => 35000, 'step' => 100, 'default' => 23500, 'unit' => 'currency']),
            $this->field('employer_match', 'Employer Contributions YTD', 'number', ['min' => 0, 'max' => 80000, 'step' => 100, 'default' => 10000, 'unit' => 'currency']),
            $this->field('compensation', 'Plan Compensation', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 200000, 'unit' => 'currency']),
            $this->field('section_415_limit', '§415 Annual Additions Limit', 'number', ['min' => 50000, 'max' => 100000, 'step' => 100, 'default' => 70000, 'unit' => 'currency', 'required' => false]),
            $this->field('years', 'Projection Years', 'number', ['min' => 1, 'max' => 40, 'step' => 1, 'default' => 20, 'unit' => 'years']),
            $this->field('expected_return', 'Expected Return', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 7, 'unit' => '%']),
            $this->field('ordinary_rate', 'Ordinary Tax Rate (for taxable compare)', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 32, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $allows = $this->toString($inputs, 'plan_allows_after_tax', 'yes') === 'yes';
        $conversion = $this->toString($inputs, 'in_plan_conversion', 'yes') === 'yes';
        $deferral = $this->requireNumeric($inputs, 'employee_deferral');
        $match = $this->requireNumeric($inputs, 'employer_match');
        $comp = $this->requireNumeric($inputs, 'compensation');
        $limit415 = $this->toFloat($inputs, 'section_415_limit', 70000);
        $years = (int) max(1, round($this->requireNumeric($inputs, 'years')));
        $r = $this->requireNumeric($inputs, 'expected_return') / 100;

        $eligible = $allows && $conversion;
        $headroom = max(0, $limit415 - $deferral - $match);
        // Also can't exceed remaining comp in some plans
        $headroom = min($headroom, max(0, $comp - $deferral));

        $fvRoth = $eligible ? $this->fvAnnuity($headroom, $r, $years) : 0.0;
        $afterTaxContrib = $headroom * $years;
        $taxableFv = $this->fvAnnuity($headroom * (1 - 0), $r, $years); // same dollars in taxable
        $taxableNet = $taxableFv - max(0, $taxableFv - $afterTaxContrib) * 0.15;

        return [
            'results' => [
                'eligible' => $eligible ? 'Yes — after-tax + conversion path available' : 'Not fully eligible — need after-tax source AND in-plan/in-service Roth path',
                'this_year_after_tax_headroom' => $this->round($headroom),
                'projected_roth_balance' => $this->round($fvRoth),
                'projected_taxable_net' => $this->round($taxableNet),
                'lifetime_roth_advantage' => $this->round($fvRoth - $taxableNet),
                'section_415_room_used' => $this->round($deferral + $match),
            ],
            'breakdown' => [
                '415_limit' => $limit415,
                'formula' => 'After-tax headroom ≈ §415 limit − employee deferrals − employer contributions; convert promptly to Roth',
            ],
            'units' => [
                'eligible' => '',
                'this_year_after_tax_headroom' => 'currency',
                'projected_roth_balance' => 'currency',
                'projected_taxable_net' => 'currency',
                'lifetime_roth_advantage' => 'currency',
                'section_415_room_used' => 'currency',
            ],
        ];
    }

    protected function fvAnnuity(float $pmt, float $r, int $n): float
    {
        if ($pmt <= 0) {
            return 0.0;
        }
        if ($r == 0.0) {
            return $pmt * $n;
        }

        return $pmt * (((1 + $r) ** $n - 1) / $r);
    }
}
