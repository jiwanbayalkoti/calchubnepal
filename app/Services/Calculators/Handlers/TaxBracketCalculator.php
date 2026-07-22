<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Tax Bracket Calculator
 * Country + income → marginal rate, effective rate, net from a $5k raise.
 */
class TaxBracketCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tax_bracket_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('country', 'Country', 'select', [
                'options' => [
                    'US' => 'United States (federal)',
                    'UK' => 'United Kingdom (income tax)',
                    'IN' => 'India (new regime illustrative)',
                    'CA' => 'Canada (federal illustrative)',
                    'AU' => 'Australia (illustrative)',
                ],
                'default' => 'US',
            ]),
            $this->field('income', 'Taxable / Assessable Income', 'number', ['min' => 0, 'max' => 10000000, 'step' => 100, 'default' => 90000, 'unit' => 'currency']),
            $this->field('filing_status', 'US Filing Status (if US)', 'select', [
                'options' => ['single' => 'Single', 'mfj' => 'Married filing jointly'],
                'default' => 'single',
                'required' => false,
            ]),
            $this->field('raise', 'Raise to Model', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 5000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $country = $this->toString($inputs, 'country', 'US');
        $income = $this->requireNumeric($inputs, 'income');
        $status = $this->toString($inputs, 'filing_status', 'single');
        $raise = $this->toFloat($inputs, 'raise', 5000);

        $brackets = $this->brackets($country, $status);
        $tax = $this->taxFromBrackets($income, $brackets);
        $tax2 = $this->taxFromBrackets($income + $raise, $brackets);
        $marginal = $this->marginalRate($income, $brackets);
        $effective = $income > 0 ? $tax / $income : 0;
        $netRaise = $raise - ($tax2 - $tax);

        return [
            'results' => [
                'marginal_rate_pct' => $this->round($marginal * 100, 1),
                'effective_rate_pct' => $this->round($effective * 100, 1),
                'income_tax' => $this->round($tax),
                'tax_on_raise' => $this->round($tax2 - $tax),
                'net_from_raise' => $this->round($netRaise),
                'country' => $country,
            ],
            'breakdown' => [
                'raise_modeled' => $raise,
                'formula' => 'Marginal = rate on next dollar; effective = total tax ÷ income; net raise = raise − Δtax',
            ],
            'units' => [
                'marginal_rate_pct' => '%',
                'effective_rate_pct' => '%',
                'income_tax' => 'currency',
                'tax_on_raise' => 'currency',
                'net_from_raise' => 'currency',
                'country' => '',
            ],
        ];
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    protected function brackets(string $country, string $status): array
    {
        return match ($country) {
            'UK' => [[12570, 0.0], [50270, 0.20], [125140, 0.40], [INF, 0.45]],
            'IN' => [[300000, 0.0], [700000, 0.05], [1000000, 0.10], [1200000, 0.15], [1500000, 0.20], [INF, 0.30]],
            'CA' => [[55867, 0.15], [111733, 0.205], [173205, 0.26], [246752, 0.29], [INF, 0.33]],
            'AU' => [[18200, 0.0], [45000, 0.16], [135000, 0.30], [190000, 0.37], [INF, 0.45]],
            default => $status === 'mfj'
                ? [[23850, 0.10], [96950, 0.12], [206700, 0.22], [394600, 0.24], [501050, 0.32], [751600, 0.35], [INF, 0.37]]
                : [[11925, 0.10], [48475, 0.12], [103350, 0.22], [197300, 0.24], [250525, 0.32], [626350, 0.35], [INF, 0.37]],
        };
    }

    /**
     * @param  array<int, array{0: float, 1: float}>  $brackets
     */
    protected function taxFromBrackets(float $income, array $brackets): float
    {
        $tax = 0.0;
        $prev = 0.0;
        foreach ($brackets as [$cap, $rate]) {
            $slice = min($income, $cap) - $prev;
            if ($slice > 0) {
                $tax += $slice * $rate;
            }
            if ($income <= $cap) {
                break;
            }
            $prev = $cap;
        }

        return $tax;
    }

    /**
     * @param  array<int, array{0: float, 1: float}>  $brackets
     */
    protected function marginalRate(float $income, array $brackets): float
    {
        $prev = 0.0;
        foreach ($brackets as [$cap, $rate]) {
            if ($income <= $cap) {
                return $rate;
            }
            $prev = $cap;
        }

        return 0.37;
    }
}
