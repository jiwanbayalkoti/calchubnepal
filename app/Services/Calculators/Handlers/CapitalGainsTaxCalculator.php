<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Capital Gains Tax Calculator
 * STCG ordinary / LTCG 0-15-20% + NIIT 3.8% + hold-vs-sold counterfactual.
 */
class CapitalGainsTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'capital_gains_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('cost_basis', 'Cost Basis', 'number', ['min' => 0, 'max' => 50000000, 'step' => 1, 'default' => 50000, 'unit' => 'currency']),
            $this->field('sale_price', 'Sale Price', 'number', ['min' => 0, 'max' => 50000000, 'step' => 1, 'default' => 80000, 'unit' => 'currency']),
            $this->field('holding_months', 'Holding Period (months)', 'number', ['min' => 0, 'max' => 600, 'step' => 1, 'default' => 8, 'unit' => 'months']),
            $this->field('agi', 'AGI (without this gain)', 'number', ['min' => 0, 'max' => 20000000, 'step' => 100, 'default' => 120000, 'unit' => 'currency']),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => ['single' => 'Single', 'mfj' => 'Married filing jointly', 'hoh' => 'Head of household'],
                'default' => 'single',
            ]),
            $this->field('state_rate', 'State Cap Gains Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $basis = $this->requireNumeric($inputs, 'cost_basis');
        $sale = $this->requireNumeric($inputs, 'sale_price');
        $months = $this->requireNumeric($inputs, 'holding_months');
        $agi = $this->requireNumeric($inputs, 'agi');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $state = $this->toFloat($inputs, 'state_rate', 5) / 100;

        $gain = max(0, $sale - $basis);
        $longTerm = $months >= 12;

        $ltcgRate = $this->ltcgRate($agi + $gain, $status);
        $fed = $longTerm ? $gain * $ltcgRate : $this->ordinaryOnGain($agi, $gain, $status);

        $niitThreshold = $status === 'mfj' ? 250000 : 200000;
        $niitBase = max(0, ($agi + $gain) - $niitThreshold);
        $niit = min($gain, $niitBase) * 0.038;

        $stateTax = $gain * $state;
        $total = $fed + $niit + $stateTax;

        // Counterfactual: hold to 12 months
        $ltcgIfHeld = $gain * $this->ltcgRate($agi + $gain, $status);
        $niitIfHeld = $niit; // same AGI assumption
        $totalIfHeld = $ltcgIfHeld + $niitIfHeld + $stateTax;
        $saveIfHold = $longTerm ? 0.0 : max(0, $total - $totalIfHeld);

        return [
            'results' => [
                'capital_gain' => $this->round($gain),
                'treatment' => $longTerm ? 'Long-term' : 'Short-term (ordinary rates)',
                'federal_capital_gains_tax' => $this->round($fed),
                'ltcg_rate_pct' => $this->round(($longTerm ? $ltcgRate : ($gain > 0 ? $fed / $gain : 0)) * 100, 1),
                'niit_3_8_pct' => $this->round($niit),
                'state_tax' => $this->round($stateTax),
                'total_tax' => $this->round($total),
                'tax_if_held_to_12_months' => $this->round($totalIfHeld),
                'savings_if_hold_to_long_term' => $this->round($saveIfHold),
            ],
            'breakdown' => [
                'formula' => 'STCG at ordinary brackets; LTCG 0/15/20% by taxable income; NIIT 3.8% above $200k/$250k MAGI',
            ],
            'units' => [
                'capital_gain' => 'currency',
                'treatment' => '',
                'federal_capital_gains_tax' => 'currency',
                'ltcg_rate_pct' => '%',
                'niit_3_8_pct' => 'currency',
                'state_tax' => 'currency',
                'total_tax' => 'currency',
                'tax_if_held_to_12_months' => 'currency',
                'savings_if_hold_to_long_term' => 'currency',
            ],
        ];
    }

    protected function ltcgRate(float $taxableProxy, string $status): float
    {
        // Approximate 2025/26 LTCG breakpoints on taxable income
        [$zero, $fifteen] = match ($status) {
            'mfj' => [96950.0, 600050.0],
            'hoh' => [64750.0, 566700.0],
            default => [48350.0, 533400.0],
        };
        if ($taxableProxy <= $zero) {
            return 0.0;
        }
        if ($taxableProxy <= $fifteen) {
            return 0.15;
        }

        return 0.20;
    }

    protected function ordinaryOnGain(float $agi, float $gain, string $status): float
    {
        $std = match ($status) {
            'mfj' => 30000.0,
            'hoh' => 22500.0,
            default => 15000.0,
        };
        $before = max(0, $agi - $std);
        $after = max(0, $agi + $gain - $std);

        return $this->progressive($after, $status) - $this->progressive($before, $status);
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
