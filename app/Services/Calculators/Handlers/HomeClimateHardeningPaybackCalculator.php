<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Home Climate-Hardening Payback Calculator
 * Per-measure payback by peril; insurance discount + damage mitigation + resale lift.
 */
class HomeClimateHardeningPaybackCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'home_climate_hardening_payback_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('peril', 'Primary Peril', 'select', [
                'options' => [
                    'hurricane' => 'Hurricane / wind',
                    'wildfire' => 'Wildfire',
                    'flood' => 'Flood',
                    'hail' => 'Hail',
                ],
                'default' => 'hurricane',
            ]),
            $this->field('measure_cost', 'Hardening Measure Cost', 'number', ['min' => 100, 'max' => 100000, 'step' => 50, 'default' => 8500, 'unit' => 'currency']),
            $this->field('annual_damage_reduction', 'Expected Annual Damage Avoided', 'number', ['min' => 0, 'max' => 50000, 'step' => 50, 'default' => 1200, 'unit' => 'currency']),
            $this->field('insurance_discount_annual', 'Annual Insurance Premium Discount', 'number', ['min' => 0, 'max' => 10000, 'step' => 25, 'default' => 450, 'unit' => 'currency', 'required' => false]),
            $this->field('resale_lift', 'Resale / Appraisal Lift', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 5000, 'unit' => 'currency', 'required' => false]),
            $this->field('hold_years', 'Hold Period Before Sale', 'number', ['min' => 1, 'max' => 30, 'step' => 1, 'default' => 10, 'unit' => 'years']),
            $this->field('discount_rate', 'Discount Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('phase2_cost', 'Optional Phase-2 Measure Cost', 'number', ['min' => 0, 'max' => 100000, 'step' => 50, 'default' => 4000, 'unit' => 'currency', 'required' => false]),
            $this->field('phase2_annual_benefit', 'Phase-2 Annual Benefit', 'number', ['min' => 0, 'max' => 20000, 'step' => 25, 'default' => 500, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $peril = $this->toString($inputs, 'peril', 'hurricane');
        $cost = $this->requireNumeric($inputs, 'measure_cost');
        $dmgAvoid = $this->requireNumeric($inputs, 'annual_damage_reduction');
        $insDisc = $this->toFloat($inputs, 'insurance_discount_annual', 0);
        $resale = $this->toFloat($inputs, 'resale_lift', 0);
        $hold = (int) max(1, round($this->requireNumeric($inputs, 'hold_years')));
        $r = $this->toFloat($inputs, 'discount_rate', 5) / 100;
        $p2Cost = $this->toFloat($inputs, 'phase2_cost', 0);
        $p2Ben = $this->toFloat($inputs, 'phase2_annual_benefit', 0);

        $annual = $dmgAvoid + $insDisc;
        $npv = -$cost;
        for ($y = 1; $y <= $hold; $y++) {
            $npv += $annual / ((1 + $r) ** $y);
        }
        $npv += $resale / ((1 + $r) ** $hold);

        $payback = $annual > 0 ? $cost / $annual : null;

        $p2Npv = 0.0;
        $p2Payback = null;
        if ($p2Cost > 0) {
            $p2Npv = -$p2Cost;
            for ($y = 1; $y <= $hold; $y++) {
                $p2Npv += $p2Ben / ((1 + $r) ** $y);
            }
            $p2Payback = $p2Ben > 0 ? $p2Cost / $p2Ben : null;
        }

        $rank = 'Do Phase 1 first';
        if ($p2Cost > 0 && $p2Payback !== null && $payback !== null) {
            $rank = $payback <= $p2Payback
                ? 'Phase 1 before Phase 2 (faster payback)'
                : 'Phase 2 pays back faster — consider reordering';
        }

        return [
            'results' => [
                'annual_total_benefit' => $this->round($annual),
                'simple_payback_years' => $payback === null ? 'n/a' : $this->round($payback, 1),
                'npv_over_hold' => $this->round($npv),
                'resale_lift_pv' => $this->round($resale / ((1 + $r) ** $hold)),
                'phase2_payback_years' => $p2Payback === null ? 'n/a' : $this->round($p2Payback, 1),
                'phase2_npv' => $this->round($p2Npv),
                'phased_roi_ranking' => $rank,
                'peril_focus' => $peril,
            ],
            'breakdown' => [
                'damage_mitigation_annual' => $this->round($dmgAvoid),
                'insurance_discount_annual' => $this->round($insDisc),
                'resale_lift' => $this->round($resale),
                'formula' => 'NPV = −cost + Σ(damage avoided + insurance discount)/(1+r)^t + resale/(1+r)^N',
            ],
            'units' => [
                'annual_total_benefit' => 'currency/yr',
                'simple_payback_years' => 'years',
                'npv_over_hold' => 'currency',
                'resale_lift_pv' => 'currency',
                'phase2_payback_years' => 'years',
                'phase2_npv' => 'currency',
                'phased_roi_ranking' => '',
                'peril_focus' => '',
            ],
        ];
    }
}
