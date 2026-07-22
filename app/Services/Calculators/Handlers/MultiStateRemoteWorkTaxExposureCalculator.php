<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Multi-State Remote Work Tax Exposure
 * Domicile + workdays by state; double-tax exposure; convenience-rule penalty.
 */
class MultiStateRemoteWorkTaxExposureCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'multi_state_remote_work_tax_exposure_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wages', 'Annual Wages', 'number', ['min' => 1, 'max' => 5000000, 'step' => 100, 'default' => 160000, 'unit' => 'currency']),
            $this->field('domicile_state', 'Domicile State', 'select', [
                'options' => [
                    'CA' => 'California', 'NY' => 'New York', 'TX' => 'Texas (no income tax)', 'FL' => 'Florida (no income tax)',
                    'WA' => 'Washington (no income tax)', 'NJ' => 'New Jersey', 'MA' => 'Massachusetts', 'IL' => 'Illinois',
                    'OTHER' => 'Other / enter rate below',
                ],
                'default' => 'CA',
            ]),
            $this->field('domicile_rate', 'Domicile Effective State Rate Override', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 0, 'unit' => '%', 'required' => false]),
            $this->field('employer_state', 'Employer / Office State', 'select', [
                'options' => [
                    'NY' => 'New York', 'CA' => 'California', 'CT' => 'Connecticut', 'DE' => 'Delaware',
                    'NE' => 'Nebraska', 'PA' => 'Pennsylvania', 'TX' => 'Texas', 'OTHER' => 'Other',
                ],
                'default' => 'NY',
            ]),
            $this->field('days_in_domicile', 'Workdays in Domicile', 'number', ['min' => 0, 'max' => 366, 'step' => 1, 'default' => 180]),
            $this->field('days_in_employer_state', 'Workdays in Employer State', 'number', ['min' => 0, 'max' => 366, 'step' => 1, 'default' => 40]),
            $this->field('days_in_other_states', 'Workdays in Other States', 'number', ['min' => 0, 'max' => 366, 'step' => 1, 'default' => 20]),
            $this->field('other_states_rate', 'Blended Other-States Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('convenience_rule', 'Employer State Has Convenience Rule?', 'select', [
                'options' => [
                    'auto' => 'Auto (NY/CT/DE/NE/PA)',
                    'yes' => 'Force yes',
                    'no' => 'Force no',
                ],
                'default' => 'auto',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wages = $this->requireNumeric($inputs, 'wages');
        $dom = $this->toString($inputs, 'domicile_state', 'CA');
        $emp = $this->toString($inputs, 'employer_state', 'NY');
        $dDom = $this->requireNumeric($inputs, 'days_in_domicile');
        $dEmp = $this->requireNumeric($inputs, 'days_in_employer_state');
        $dOth = $this->requireNumeric($inputs, 'days_in_other_states');
        $otherRate = $this->toFloat($inputs, 'other_states_rate', 5) / 100;
        $convMode = $this->toString($inputs, 'convenience_rule', 'auto');

        $rates = [
            'CA' => 0.093, 'NY' => 0.0685, 'NJ' => 0.0637, 'MA' => 0.05, 'IL' => 0.0495,
            'CT' => 0.0699, 'DE' => 0.066, 'NE' => 0.0584, 'PA' => 0.0307,
            'TX' => 0.0, 'FL' => 0.0, 'WA' => 0.0, 'OTHER' => 0.05,
        ];
        $domRate = $this->toFloat($inputs, 'domicile_rate', 0) / 100;
        if ($domRate <= 0) {
            $domRate = $rates[$dom] ?? 0.05;
        }
        $empRate = $rates[$emp] ?? 0.05;

        $totalDays = max(1, $dDom + $dEmp + $dOth);
        $convStates = ['NY', 'CT', 'DE', 'NE', 'PA'];
        $convenience = $convMode === 'yes' || ($convMode === 'auto' && in_array($emp, $convStates, true));

        // Under convenience rule, remote days in domicile may still be sourced to employer state
        $sourcedEmpDays = $dEmp + ($convenience ? $dDom : 0);
        $sourcedDomDays = $convenience ? 0 : $dDom;
        $sourcedOthDays = $dOth;

        $taxEmp = $wages * ($sourcedEmpDays / $totalDays) * $empRate;
        $taxDom = $wages * ($sourcedDomDays / $totalDays) * $domRate;
        $taxOth = $wages * ($sourcedOthDays / $totalDays) * $otherRate;

        // Resident state typically taxes all income with credit for taxes paid to other states
        $residentTax = $wages * $domRate;
        $credit = min($residentTax, $taxEmp + $taxOth);
        $totalTax = $residentTax + max(0, ($taxEmp + $taxOth) - $credit);

        // Double-tax exposure if credit incomplete (nonresident vs resident mismatch)
        $grossWithoutCredit = $taxDom + $taxEmp + $taxOth;
        $doubleTaxExposure = max(0, $grossWithoutCredit - $totalTax);

        $conveniencePenalty = $convenience
            ? $wages * ($dDom / $totalDays) * $empRate
            : 0.0;

        return [
            'results' => [
                'total_state_tax_owed' => $this->round($totalTax),
                'employer_state_tax' => $this->round($taxEmp),
                'domicile_resident_tax_before_credit' => $this->round($residentTax),
                'credit_for_taxes_paid_elsewhere' => $this->round($credit),
                'double_taxation_exposure' => $this->round($doubleTaxExposure),
                'convenience_rule_penalty' => $this->round($conveniencePenalty),
                'convenience_rule_applies' => $convenience ? 'Yes' : 'No',
                'record_keeping' => 'Keep daily location log, employer remote policy, travel calendar, and payroll state withholdings for each workday.',
            ],
            'breakdown' => [
                'sourced_days_employer' => $sourcedEmpDays,
                'sourced_days_domicile' => $sourcedDomDays,
                'sourced_days_other' => $sourcedOthDays,
                'formula' => 'Allocate wages by workdays; resident taxes worldwide with credit; convenience-rule states may source remote domicile days to employer state',
            ],
            'units' => [
                'total_state_tax_owed' => 'currency',
                'employer_state_tax' => 'currency',
                'domicile_resident_tax_before_credit' => 'currency',
                'credit_for_taxes_paid_elsewhere' => 'currency',
                'double_taxation_exposure' => 'currency',
                'convenience_rule_penalty' => 'currency',
                'convenience_rule_applies' => '',
                'record_keeping' => '',
            ],
        ];
    }
}
