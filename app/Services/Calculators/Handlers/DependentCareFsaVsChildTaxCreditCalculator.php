<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Dependent Care FSA vs Child Tax Credit Calculator
 * Optimal DCFSA + residual CDCC (Form 2441) vs CDCC alone.
 */
class DependentCareFsaVsChildTaxCreditCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'dependent_care_fsa_vs_child_tax_credit_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('care_expenses', 'Annual Dependent Care Expenses', 'number', ['min' => 0, 'max' => 50000, 'step' => 100, 'default' => 12000, 'unit' => 'currency']),
            $this->field('qualifying_dependents', 'Qualifying Dependents (under 13)', 'number', ['min' => 1, 'max' => 5, 'step' => 1, 'default' => 2]),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => ['single' => 'Single / HOH', 'mfj' => 'Married filing jointly'],
                'default' => 'mfj',
            ]),
            $this->field('agi', 'AGI', 'number', ['min' => 0, 'max' => 1000000, 'step' => 100, 'default' => 110000, 'unit' => 'currency']),
            $this->field('marginal_rate', 'Marginal Tax Rate (for FSA savings)', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 22, 'unit' => '%']),
            $this->field('employer_fsa_limit', 'Employer DCFSA Limit', 'number', ['min' => 0, 'max' => 5000, 'step' => 50, 'default' => 5000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $expenses = $this->requireNumeric($inputs, 'care_expenses');
        $deps = (int) max(1, round($this->requireNumeric($inputs, 'qualifying_dependents')));
        $status = $this->toString($inputs, 'filing_status', 'mfj');
        $agi = $this->requireNumeric($inputs, 'agi');
        $marginal = $this->requireNumeric($inputs, 'marginal_rate') / 100;
        $fsaLimit = $this->toFloat($inputs, 'employer_fsa_limit', 5000);
        if ($status !== 'mfj') {
            $fsaLimit = min($fsaLimit, 2500); // illustrative single limit historically $2.5k; post-secure may vary — keep employer field
        }

        $cdccMaxExpenses = min($expenses, $deps >= 2 ? 6000 : 3000);
        // CDCC rate phases from 35% to 20% above AGI ~$15k (simplified)
        $cdccRate = 0.35;
        if ($agi > 15000) {
            $steps = min(15, (int) floor(($agi - 15000) / 2000));
            $cdccRate = max(0.20, 0.35 - ($steps * 0.01));
        }
        $cdccAlone = $cdccMaxExpenses * $cdccRate;

        $optimalFsa = min($expenses, $fsaLimit);
        $fsaTaxSave = $optimalFsa * $marginal; // + FICA approx
        $fsaFicaSave = $optimalFsa * 0.0765;
        $remainingExpenses = max(0, $expenses - $optimalFsa);
        $cdccResidualBase = min($remainingExpenses, max(0, ($deps >= 2 ? 6000 : 3000) - $optimalFsa));
        // IRS: expenses reduced by FSA; CDCC on leftover up to limits
        $cdccResidual = max(0, $cdccResidualBase) * $cdccRate;
        $totalWithFsa = $fsaTaxSave + $fsaFicaSave + $cdccResidual;

        return [
            'results' => [
                'optimal_dcfsa_contribution' => $this->round($optimalFsa),
                'fsa_income_tax_savings' => $this->round($fsaTaxSave),
                'fsa_fica_savings' => $this->round($fsaFicaSave),
                'residual_cdcc_form_2441' => $this->round($cdccResidual),
                'total_tax_saved_with_fsa' => $this->round($totalWithFsa),
                'cdcc_alone' => $this->round($cdccAlone),
                'advantage_vs_cdcc_alone' => $this->round($totalWithFsa - $cdccAlone),
            ],
            'breakdown' => [
                'cdcc_rate_pct' => $this->round($cdccRate * 100, 1),
                'formula' => 'Max FSA first (excludes from wages); claim CDCC on remaining eligible expenses via Form 2441',
            ],
            'units' => [
                'optimal_dcfsa_contribution' => 'currency',
                'fsa_income_tax_savings' => 'currency',
                'fsa_fica_savings' => 'currency',
                'residual_cdcc_form_2441' => 'currency',
                'total_tax_saved_with_fsa' => 'currency',
                'cdcc_alone' => 'currency',
                'advantage_vs_cdcc_alone' => 'currency',
            ],
        ];
    }
}
