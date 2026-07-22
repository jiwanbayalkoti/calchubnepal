<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Bonus Tax Calculator
 * 22% supplemental flat withholding vs real marginal tax; refund vs balance-due.
 */
class BonusTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'bonus_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('salary', 'Annual Salary (W-2 wages before bonus)', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 120000, 'unit' => 'currency']),
            $this->field('bonus', 'Bonus Amount', 'number', ['min' => 0, 'max' => 2000000, 'step' => 100, 'default' => 25000, 'unit' => 'currency']),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => [
                    'single' => 'Single',
                    'mfj' => 'Married filing jointly',
                    'hoh' => 'Head of household',
                ],
                'default' => 'single',
            ]),
            $this->field('state_rate', 'State Marginal Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('pretax_deductions', 'Annual Pre-Tax Deductions (401k/HSA)', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 15000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $salary = $this->requireNumeric($inputs, 'salary');
        $bonus = $this->requireNumeric($inputs, 'bonus');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $state = $this->toFloat($inputs, 'state_rate', 5) / 100;
        $pretax = $this->toFloat($inputs, 'pretax_deductions', 0);

        $std = match ($status) {
            'mfj' => 30000.0,
            'hoh' => 22500.0,
            default => 15000.0,
        };

        $taxableWithout = max(0, $salary - $pretax - $std);
        $taxableWith = max(0, $salary + $bonus - $pretax - $std);

        $fedWithout = $this->federalTax($taxableWithout, $status);
        $fedWith = $this->federalTax($taxableWith, $status);
        $trueFedOnBonus = $fedWith - $fedWithout;
        $trueMarginal = $bonus > 0 ? $trueFedOnBonus / $bonus : 0;

        $flatWithhold = $bonus * 0.22;
        $stateWithhold = $bonus * $state;
        $fica = $bonus * 0.0765; // illustrative SS+Med until wage base

        $trueTotalOnBonus = $trueFedOnBonus + ($bonus * $state) + $fica;
        $withheldTotal = $flatWithhold + $stateWithhold + $fica;
        $delta = $withheldTotal - $trueTotalOnBonus;

        $verdict = abs($delta) < 50
            ? 'About even — withholding ≈ true tax on the bonus'
            : ($delta > 0
                ? 'Likely refund — flat 22% withheld more than your marginal rate on the bonus'
                : 'Likely owe more at filing — your marginal rate exceeds the 22% supplemental flat');

        return [
            'results' => [
                'supplemental_flat_federal_withholding' => $this->round($flatWithhold),
                'true_federal_tax_on_bonus' => $this->round($trueFedOnBonus),
                'true_marginal_federal_rate_pct' => $this->round($trueMarginal * 100, 1),
                'total_withheld_on_bonus_est' => $this->round($withheldTotal),
                'true_total_tax_on_bonus_est' => $this->round($trueTotalOnBonus),
                'refund_or_balance_due' => $this->round($delta),
                'verdict' => $verdict,
            ],
            'breakdown' => [
                'federal_tax_without_bonus' => $this->round($fedWithout),
                'federal_tax_with_bonus' => $this->round($fedWith),
                'state_withholding_est' => $this->round($stateWithhold),
                'fica_on_bonus_est' => $this->round($fica),
                'formula' => 'True tax = tax(salary+bonus) − tax(salary); compare to 22% IRS supplemental flat withholding',
            ],
            'units' => [
                'supplemental_flat_federal_withholding' => 'currency',
                'true_federal_tax_on_bonus' => 'currency',
                'true_marginal_federal_rate_pct' => '%',
                'total_withheld_on_bonus_est' => 'currency',
                'true_total_tax_on_bonus_est' => 'currency',
                'refund_or_balance_due' => 'currency',
                'verdict' => '',
            ],
        ];
    }

    protected function federalTax(float $taxable, string $status): float
    {
        $brackets = $status === 'mfj'
            ? [[23850, 0.10], [96950, 0.12], [206700, 0.22], [394600, 0.24], [501050, 0.32], [751600, 0.35], [INF, 0.37]]
            : ($status === 'hoh'
                ? [[17000, 0.10], [64850, 0.12], [103350, 0.22], [197300, 0.24], [250500, 0.32], [626350, 0.35], [INF, 0.37]]
                : [[11925, 0.10], [48475, 0.12], [103350, 0.22], [197300, 0.24], [250525, 0.32], [626350, 0.35], [INF, 0.37]]);

        $tax = 0.0;
        $prev = 0.0;
        foreach ($brackets as [$cap, $rate]) {
            $slice = min($taxable, $cap) - $prev;
            if ($slice > 0) {
                $tax += $slice * $rate;
            }
            if ($taxable <= $cap) {
                break;
            }
            $prev = $cap;
        }

        return $tax;
    }
}
