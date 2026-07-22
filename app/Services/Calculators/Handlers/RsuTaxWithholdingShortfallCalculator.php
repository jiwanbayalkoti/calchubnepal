<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * RSU Tax & Withholding Shortfall Calculator
 * 22% supplemental flat vs real marginal; cumulative shortfall across vests.
 */
class RsuTaxWithholdingShortfallCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rsu_tax_withholding_shortfall_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('salary', 'Annual Salary', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 180000, 'unit' => 'currency']),
            $this->field('rsu_vest', 'This Vest Value (gross)', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 40000, 'unit' => 'currency']),
            $this->field('ytd_prior_vests', 'YTD Prior RSU Vests', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 60000, 'unit' => 'currency']),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => ['single' => 'Single', 'mfj' => 'Married filing jointly'],
                'default' => 'single',
            ]),
            $this->field('state_rate', 'State Supplemental Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 6, 'unit' => '%', 'required' => false]),
            $this->field('pretax', 'Annual Pre-Tax Deductions', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 23000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $salary = $this->requireNumeric($inputs, 'salary');
        $vest = $this->requireNumeric($inputs, 'rsu_vest');
        $ytd = $this->requireNumeric($inputs, 'ytd_prior_vests');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $state = $this->toFloat($inputs, 'state_rate', 6) / 100;
        $pretax = $this->toFloat($inputs, 'pretax', 0);

        $std = $status === 'mfj' ? 30000.0 : 15000.0;
        $base = max(0, $salary - $pretax - $std);

        $trueOnThisVest = $this->progressive($base + $ytd + $vest, $status) - $this->progressive($base + $ytd, $status);
        $flat = $vest * 0.22;
        $shortfallThis = $trueOnThisVest - $flat;

        $trueYtdAll = $this->progressive($base + $ytd + $vest, $status) - $this->progressive($base, $status);
        $flatYtdAll = ($ytd + $vest) * 0.22;
        $cumulativeShortfall = $trueYtdAll - $flatYtdAll;

        $stateFlat = $vest * $state;

        return [
            'results' => [
                'supplemental_flat_22_pct' => $this->round($flat),
                'true_federal_tax_on_this_vest' => $this->round($trueOnThisVest),
                'shortfall_this_vest' => $this->round($shortfallThis),
                'cumulative_shortfall_ytd' => $this->round($cumulativeShortfall),
                'state_withholding_est' => $this->round($stateFlat),
                'april_balance_risk' => $cumulativeShortfall > 2000
                    ? 'Elevated — FAANG-tier April balance likely if you do not increase withholding/estimates'
                    : 'Moderate/low — monitor remaining vests',
            ],
            'breakdown' => [
                'true_federal_on_all_vests_ytd' => $this->round($trueYtdAll),
                'flat_withheld_all_vests_ytd' => $this->round($flatYtdAll),
                'formula' => 'Shortfall = true marginal tax on vest − 22% supplemental flat withholding',
            ],
            'units' => [
                'supplemental_flat_22_pct' => 'currency',
                'true_federal_tax_on_this_vest' => 'currency',
                'shortfall_this_vest' => 'currency',
                'cumulative_shortfall_ytd' => 'currency',
                'state_withholding_est' => 'currency',
                'april_balance_risk' => '',
            ],
        ];
    }

    protected function progressive(float $taxable, string $status): float
    {
        $brackets = $status === 'mfj'
            ? [[23850, 0.10], [96950, 0.12], [206700, 0.22], [394600, 0.24], [501050, 0.32], [751600, 0.35], [INF, 0.37]]
            : [[11925, 0.10], [48475, 0.12], [103350, 0.22], [197300, 0.24], [250525, 0.32], [626350, 0.35], [INF, 0.37]];
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
