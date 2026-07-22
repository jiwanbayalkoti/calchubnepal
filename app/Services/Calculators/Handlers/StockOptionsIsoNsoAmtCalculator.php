<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Stock Options ISO/NSO + AMT Calculator
 * Exercise tax + cash needed; Form 6251 AMT illustrative math.
 */
class StockOptionsIsoNsoAmtCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'stock_options_iso_nso_amt_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('option_type', 'Option Type', 'select', [
                'options' => ['iso' => 'ISO', 'nso' => 'NSO / NQSO'],
                'default' => 'iso',
            ]),
            $this->field('shares', 'Shares Exercised', 'number', ['min' => 1, 'max' => 1000000, 'step' => 1, 'default' => 2000]),
            $this->field('strike', 'Strike Price', 'number', ['min' => 0, 'max' => 10000, 'step' => 0.01, 'default' => 5, 'unit' => 'currency']),
            $this->field('fmv', 'FMV at Exercise', 'number', ['min' => 0, 'max' => 10000, 'step' => 0.01, 'default' => 40, 'unit' => 'currency']),
            $this->field('other_taxable_income', 'Other Taxable Income', 'number', ['min' => 0, 'max' => 10000000, 'step' => 100, 'default' => 150000, 'unit' => 'currency']),
            $this->field('filing_status', 'Filing Status', 'select', [
                'options' => ['single' => 'Single', 'mfj' => 'Married filing jointly'],
                'default' => 'single',
            ]),
            $this->field('state_rate', 'State Rate on Ordinary Income', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $type = $this->toString($inputs, 'option_type', 'iso');
        $shares = $this->requireNumeric($inputs, 'shares');
        $strike = $this->requireNumeric($inputs, 'strike');
        $fmv = $this->requireNumeric($inputs, 'fmv');
        $other = $this->requireNumeric($inputs, 'other_taxable_income');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $state = $this->toFloat($inputs, 'state_rate', 5) / 100;

        $spread = max(0, $fmv - $strike) * $shares;
        $exerciseCash = $strike * $shares;

        if ($type === 'nso') {
            $ordinary = $spread;
            $federal = $this->marginalOn($other, $ordinary, $status);
            $fica = $spread * 0.0765;
            $stateTax = $spread * $state;
            $amt = 0.0;
            $taxAtExercise = $federal + $fica + $stateTax;
            $note = 'NSO: bargain element is W-2 ordinary income at exercise (federal + FICA + state).';
        } else {
            // ISO: no regular tax if holding requirements met; AMT on bargain element
            $ordinary = 0.0;
            $federal = 0.0;
            $fica = 0.0;
            $stateTax = 0.0;
            $amtExemption = $status === 'mfj' ? 133300.0 : 85700.0; // illustrative
            $amti = $other + $spread;
            $amtBase = max(0, $amti - $amtExemption);
            $tentativeAmt = $amtBase <= ($status === 'mfj' ? 220700.0 : 110350.0)
                ? $amtBase * 0.26
                : (($status === 'mfj' ? 220700.0 : 110350.0) * 0.26) + (($amtBase - ($status === 'mfj' ? 220700.0 : 110350.0)) * 0.28);
            $regularTax = $this->progressive($other, $status);
            $amt = max(0, $tentativeAmt - $regularTax);
            $taxAtExercise = $amt;
            $note = 'ISO: typically no regular income tax at exercise; AMT may apply on (FMV−strike)×shares (Form 6251).';
        }

        $cashNeeded = $exerciseCash + $taxAtExercise;

        return [
            'results' => [
                'bargain_element_spread' => $this->round($spread),
                'tax_owed_at_exercise' => $this->round($taxAtExercise),
                'amt_preference_iso' => $this->round($amt),
                'fica_if_nso' => $this->round($fica),
                'cash_to_exercise' => $this->round($exerciseCash),
                'total_cash_needed_on_hand' => $this->round($cashNeeded),
                'option_type' => strtoupper($type),
                'note' => $note,
            ],
            'breakdown' => [
                'formula' => 'NSO tax ≈ ordinary on spread + FICA; ISO AMT ≈ max(0, tentative AMT − regular tax) on spread preference',
            ],
            'units' => [
                'bargain_element_spread' => 'currency',
                'tax_owed_at_exercise' => 'currency',
                'amt_preference_iso' => 'currency',
                'fica_if_nso' => 'currency',
                'cash_to_exercise' => 'currency',
                'total_cash_needed_on_hand' => 'currency',
                'option_type' => '',
                'note' => '',
            ],
        ];
    }

    protected function marginalOn(float $base, float $extra, string $status): float
    {
        return $this->progressive($base + $extra, $status) - $this->progressive($base, $status);
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
