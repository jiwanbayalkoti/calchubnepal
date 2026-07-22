<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Flood Insurance vs Self-Insure Calculator
 * Expected annual loss × FEMA zone probability; NFIP vs private; coverage gap.
 */
class FloodInsuranceVsSelfInsureCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'flood_insurance_vs_self_insure_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('home_value', 'Home Replacement Value', 'number', ['min' => 50000, 'max' => 5000000, 'step' => 1000, 'default' => 450000, 'unit' => 'currency']),
            $this->field('contents_value', 'Contents Value', 'number', ['min' => 0, 'max' => 500000, 'step' => 1000, 'default' => 75000, 'unit' => 'currency', 'required' => false]),
            $this->field('fema_zone', 'FEMA Flood Zone', 'select', [
                'options' => [
                    'x' => 'Zone X (minimal — ~0.2%/yr)',
                    'ae' => 'Zone AE / A (1%/yr SFHA)',
                    've' => 'Zone VE (coastal high — ~1.5%/yr)',
                    'shaded_x' => 'Shaded X (0.2–1% transitional)',
                ],
                'default' => 'ae',
            ]),
            $this->field('depth_damage_pct', 'Expected Damage If Flood (% of structure)', 'number', ['min' => 5, 'max' => 80, 'step' => 1, 'default' => 25, 'unit' => '%', 'required' => false]),
            $this->field('nfip_annual_premium', 'NFIP Annual Premium', 'number', ['min' => 0, 'max' => 20000, 'step' => 25, 'default' => 1800, 'unit' => 'currency']),
            $this->field('private_annual_premium', 'Private Flood Premium', 'number', ['min' => 0, 'max' => 30000, 'step' => 25, 'default' => 2400, 'unit' => 'currency']),
            $this->field('nfip_building_limit', 'NFIP Building Coverage Limit', 'number', ['min' => 0, 'max' => 250000, 'step' => 1000, 'default' => 250000, 'unit' => 'currency', 'required' => false]),
            $this->field('nfip_contents_limit', 'NFIP Contents Limit', 'number', ['min' => 0, 'max' => 100000, 'step' => 1000, 'default' => 100000, 'unit' => 'currency', 'required' => false]),
            $this->field('private_coverage', 'Private Total Coverage', 'number', ['min' => 0, 'max' => 5000000, 'step' => 1000, 'default' => 525000, 'unit' => 'currency', 'required' => false]),
            $this->field('horizon_years', 'Analysis Horizon', 'number', ['min' => 5, 'max' => 30, 'step' => 1, 'default' => 10, 'unit' => 'years']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $home = $this->requireNumeric($inputs, 'home_value');
        $contents = $this->toFloat($inputs, 'contents_value', 75000);
        $zone = $this->toString($inputs, 'fema_zone', 'ae');
        $dmgPct = $this->toFloat($inputs, 'depth_damage_pct', 25) / 100;
        $nfipPrem = $this->requireNumeric($inputs, 'nfip_annual_premium');
        $privPrem = $this->requireNumeric($inputs, 'private_annual_premium');
        $nfipBldg = $this->toFloat($inputs, 'nfip_building_limit', 250000);
        $nfipCont = $this->toFloat($inputs, 'nfip_contents_limit', 100000);
        $privCov = $this->toFloat($inputs, 'private_coverage', $home + $contents);
        $years = (int) max(1, round($this->requireNumeric($inputs, 'horizon_years')));

        $prob = match ($zone) {
            'x' => 0.002,
            'shaded_x' => 0.005,
            've' => 0.015,
            default => 0.01,
        };

        $lossGivenFlood = ($home * $dmgPct) + ($contents * min(1, $dmgPct * 1.2));
        $eal = $prob * $lossGivenFlood; // expected annual loss

        $nfipCovered = min($lossGivenFlood, $nfipBldg + $nfipCont);
        $nfipGap = max(0, $lossGivenFlood - $nfipCovered);
        $privCovered = min($lossGivenFlood, $privCov);
        $privGap = max(0, $lossGivenFlood - $privCovered);

        $selfInsureCost = $eal * $years;
        $nfipCost = ($nfipPrem * $years) + ($prob * $years * $nfipGap); // premiums + residual gap risk
        $privCost = ($privPrem * $years) + ($prob * $years * $privGap);

        $best = 'Self-insure (retain risk)';
        $bestCost = $selfInsureCost;
        if ($nfipCost < $bestCost) {
            $best = 'NFIP';
            $bestCost = $nfipCost;
        }
        if ($privCost < $bestCost) {
            $best = 'Private flood';
            $bestCost = $privCost;
        }

        return [
            'results' => [
                'expected_annual_loss' => $this->round($eal),
                'loss_if_flood_occurs' => $this->round($lossGivenFlood),
                'nfip_coverage_gap' => $this->round($nfipGap),
                'private_coverage_gap' => $this->round($privGap),
                'self_insure_horizon_cost' => $this->round($selfInsureCost),
                'nfip_horizon_cost' => $this->round($nfipCost),
                'private_horizon_cost' => $this->round($privCost),
                'recommended' => $best,
                'high_value_nfip_gap_flag' => $nfipGap > 50000 ? 'Yes — NFIP limits leave large gap on this home' : 'No material NFIP gap at assumed depth',
            ],
            'breakdown' => [
                'annual_flood_probability' => $prob,
                'fema_zone' => $zone,
                'horizon_years' => $years,
                'formula' => 'EAL = P(flood) × loss_given_flood; horizon cost ≈ premiums×years + P×years×uninsured_gap',
            ],
            'units' => [
                'expected_annual_loss' => 'currency/yr',
                'loss_if_flood_occurs' => 'currency',
                'nfip_coverage_gap' => 'currency',
                'private_coverage_gap' => 'currency',
                'self_insure_horizon_cost' => 'currency',
                'nfip_horizon_cost' => 'currency',
                'private_horizon_cost' => 'currency',
                'recommended' => '',
                'high_value_nfip_gap_flag' => '',
            ],
        ];
    }
}
