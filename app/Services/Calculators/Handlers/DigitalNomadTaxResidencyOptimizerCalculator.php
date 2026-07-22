<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Digital Nomad Tax Residency Optimizer
 * US-stay vs FEIE vs FTC across destinations; best route + savings.
 */
class DigitalNomadTaxResidencyOptimizerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'digital_nomad_tax_residency_optimizer_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('foreign_earned_income', 'Foreign Earned Income', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 120000, 'unit' => 'currency']),
            $this->field('us_days', 'Days Physically in the US', 'number', ['min' => 0, 'max' => 366, 'step' => 1, 'default' => 30]),
            $this->field('destination', 'Primary Nomad Destination', 'select', [
                'options' => [
                    'PT' => 'Portugal', 'ES' => 'Spain', 'MX' => 'Mexico', 'TH' => 'Thailand',
                    'ID' => 'Indonesia / Bali', 'AE' => 'UAE', 'EE' => 'Estonia', 'GE' => 'Georgia',
                    'CR' => 'Costa Rica', 'CO' => 'Colombia', 'MY' => 'Malaysia', 'HR' => 'Croatia',
                ],
                'default' => 'PT',
            ]),
            $this->field('foreign_tax_paid', 'Foreign Income Tax Paid', 'number', ['min' => 0, 'max' => 1000000, 'step' => 100, 'default' => 15000, 'unit' => 'currency']),
            $this->field('self_employment', 'Self-Employment / Sole Prop?', 'select', [
                'options' => ['yes' => 'Yes (SE tax may apply)', 'no' => 'No (W-2 / corp)'],
                'default' => 'yes',
            ]),
            $this->field('state_shedding', 'Successfully Shed US State Residency?', 'select', [
                'options' => ['yes' => 'Yes', 'no' => 'No — still a state resident'],
                'default' => 'no',
            ]),
            $this->field('state_rate', 'US State Rate if Not Shed', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('feie_limit', 'FEIE Exclusion Limit', 'number', ['min' => 0, 'max' => 200000, 'step' => 100, 'default' => 130000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $fei = $this->requireNumeric($inputs, 'foreign_earned_income');
        $usDays = $this->requireNumeric($inputs, 'us_days');
        $dest = $this->toString($inputs, 'destination', 'PT');
        $foreignTax = $this->requireNumeric($inputs, 'foreign_tax_paid');
        $se = $this->toString($inputs, 'self_employment', 'yes') === 'yes';
        $shed = $this->toString($inputs, 'state_shedding', 'no') === 'yes';
        $stateRate = $this->toFloat($inputs, 'state_rate', 5) / 100;
        $feieLimit = $this->toFloat($inputs, 'feie_limit', 130000);

        $physicalPresence = $usDays <= 35; // rough: need 330 full days abroad in 12 months
        $bonaFide = $usDays <= 60; // illustrative proxy

        // Baseline: US tax on all income as if resident (no FEIE)
        $baselineFed = $this->usTax($fei);
        $baselineSe = $se ? $fei * 0.9235 * 0.153 * 0.5 : 0; // employer-equiv half approx already; use full SE illustrative
        $baselineSe = $se ? min($fei, 176100) * 0.124 * 0.5 + $fei * 0.029 : 0; // simplified
        $baselineState = $shed ? 0 : $fei * $stateRate;
        $baseline = $baselineFed + $baselineSe + $baselineState;

        // FEIE route
        $excluded = min($fei, $feieLimit);
        $feieTaxable = max(0, $fei - $excluded);
        $feieFed = $this->usTax($feieTaxable);
        // FEIE doesn't remove SE tax
        $feieTotal = $feieFed + $baselineSe + $baselineState;
        $feieEligible = $physicalPresence || $bonaFide;

        // FTC route
        $ftcFed = max(0, $baselineFed - min($foreignTax, $baselineFed));
        $ftcTotal = $ftcFed + $baselineSe + $baselineState;

        $routes = [
            'US stay / no relief' => $baseline,
            'FEIE (Form 2555)' => $feieEligible ? $feieTotal : $baseline + 1,
            'FTC (Form 1116)' => $ftcTotal,
        ];
        asort($routes);
        $best = array_key_first($routes);
        $bestTax = $routes[$best];
        $savings = $baseline - $bestTax;

        return [
            'results' => [
                'baseline_us_tax_burden' => $this->round($baseline),
                'feie_route_tax' => $this->round($feieEligible ? $feieTotal : $baseline),
                'ftc_route_tax' => $this->round($ftcTotal),
                'best_route' => $best,
                'savings_vs_baseline' => $this->round(max(0, $savings)),
                'physical_presence_eligible' => $physicalPresence ? 'Likely yes' : 'Unlikely — too many US days',
                'bona_fide_residence_proxy' => $bonaFide ? 'Possibly' : 'Unlikely at these US days',
                'destination' => $dest,
                'state_shedding' => $shed ? 'Assumed shed' : 'State tax still included',
            ],
            'breakdown' => [
                'feie_exclusion_applied' => $this->round($excluded),
                'foreign_tax_credit_used' => $this->round(min($foreignTax, $baselineFed)),
                'note' => 'Illustrative planner — treaties, housing exclusion, and SE tax exceptions vary. Confirm with a cross-border CPA.',
                'formula' => 'Compare baseline vs FEIE (exclude ≤ limit) vs FTC (credit foreign tax against US tax)',
            ],
            'units' => [
                'baseline_us_tax_burden' => 'currency',
                'feie_route_tax' => 'currency',
                'ftc_route_tax' => 'currency',
                'best_route' => '',
                'savings_vs_baseline' => 'currency',
                'physical_presence_eligible' => '',
                'bona_fide_residence_proxy' => '',
                'destination' => '',
                'state_shedding' => '',
            ],
        ];
    }

    protected function usTax(float $taxable): float
    {
        $brackets = [[11925, 0.10], [48475, 0.12], [103350, 0.22], [197300, 0.24], [250525, 0.32], [626350, 0.35], [INF, 0.37]];
        $tax = 0.0;
        $prev = 0.0;
        $income = max(0, $taxable - 15000); // rough std ded
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
}
