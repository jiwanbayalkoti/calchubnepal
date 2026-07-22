<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Wildfire Defensible Space ROI
 * Annual expected-loss reduction, 10-yr NPV, insurance non-renewal flag, Cal Fire zones.
 */
class WildfireDefensibleSpaceRoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'wildfire_defensible_space_roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('home_value', 'Home Value', 'number', ['min' => 50000, 'max' => 5000000, 'step' => 1000, 'default' => 550000, 'unit' => 'currency']),
            $this->field('wui_risk', 'WUI Risk Band', 'select', [
                'options' => [
                    'moderate' => 'Moderate WUI',
                    'high' => 'High WUI',
                    'very_high' => 'Very high / Fire Hazard Severity',
                ],
                'default' => 'high',
            ]),
            $this->field('zone0_cost', 'Zone 0 (0–5 ft) Work Cost', 'number', ['min' => 0, 'max' => 30000, 'step' => 50, 'default' => 3500, 'unit' => 'currency']),
            $this->field('zone1_cost', 'Zone 1 (5–30 ft) Work Cost', 'number', ['min' => 0, 'max' => 40000, 'step' => 50, 'default' => 6000, 'unit' => 'currency']),
            $this->field('zone2_cost', 'Zone 2 (30–100 ft) Work Cost', 'number', ['min' => 0, 'max' => 50000, 'step' => 50, 'default' => 4500, 'unit' => 'currency', 'required' => false]),
            $this->field('annual_loss_reduction_pct', 'Expected Annual Loss Reduction', 'number', ['min' => 0.1, 'max' => 5, 'step' => 0.1, 'default' => 0.8, 'unit' => '% of home value', 'required' => false]),
            $this->field('insurance_discount', 'Annual Insurance Discount After Work', 'number', ['min' => 0, 'max' => 10000, 'step' => 25, 'default' => 600, 'unit' => 'currency', 'required' => false]),
            $this->field('annual_maintenance', 'Annual Defensible-Space Maintenance', 'number', ['min' => 0, 'max' => 5000, 'step' => 25, 'default' => 400, 'unit' => 'currency', 'required' => false]),
            $this->field('discount_rate', 'Discount Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('currently_compliant', 'Cal Fire Zone 0/1/2 Compliant?', 'select', [
                'options' => ['no' => 'Not yet', 'partial' => 'Partial', 'yes' => 'Yes'],
                'default' => 'no',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $home = $this->requireNumeric($inputs, 'home_value');
        $wui = $this->toString($inputs, 'wui_risk', 'high');
        $z0 = $this->requireNumeric($inputs, 'zone0_cost');
        $z1 = $this->requireNumeric($inputs, 'zone1_cost');
        $z2 = $this->toFloat($inputs, 'zone2_cost', 0);
        $lossPct = $this->toFloat($inputs, 'annual_loss_reduction_pct', 0.8) / 100;
        $insDisc = $this->toFloat($inputs, 'insurance_discount', 0);
        $maint = $this->toFloat($inputs, 'annual_maintenance', 400);
        $r = $this->toFloat($inputs, 'discount_rate', 5) / 100;
        $compliant = $this->toString($inputs, 'currently_compliant', 'no');

        // Higher WUI → slightly higher assumed loss reduction benefit if mitigated
        $wuiMult = match ($wui) {
            'very_high' => 1.35,
            'moderate' => 0.75,
            default => 1.0,
        };

        $totalCost = $z0 + $z1 + $z2;
        $annualLossReduction = $home * $lossPct * $wuiMult;
        $annualNet = $annualLossReduction + $insDisc - $maint;

        $npv = -$totalCost;
        for ($y = 1; $y <= 10; $y++) {
            $npv += $annualNet / ((1 + $r) ** $y);
        }

        $nonRenewal = ($wui === 'very_high' || $wui === 'high') && $compliant !== 'yes'
            ? 'FLAG — high-WUI + non-compliant raises insurance non-renewal risk'
            : 'Lower non-renewal risk if Zone 0/1/2 maintained';

        $payback = $annualNet > 0 ? $totalCost / $annualNet : null;

        return [
            'results' => [
                'total_defensible_space_cost' => $this->round($totalCost),
                'annual_expected_loss_reduction' => $this->round($annualLossReduction),
                'annual_net_benefit' => $this->round($annualNet),
                'npv_10_year' => $this->round($npv),
                'simple_payback_years' => $payback === null ? 'n/a' : $this->round($payback, 1),
                'insurance_non_renewal_flag' => $nonRenewal,
                'cal_fire_priority' => 'Complete Zone 0 first, then Zone 1, then Zone 2',
                'compliance_status' => $compliant,
            ],
            'breakdown' => [
                'zone_0_cost' => $this->round($z0),
                'zone_1_cost' => $this->round($z1),
                'zone_2_cost' => $this->round($z2),
                'wui_risk' => $wui,
                'formula' => 'Annual loss reduction ≈ home value × reduction% × WUI multiplier; NPV over 10 years',
            ],
            'units' => [
                'total_defensible_space_cost' => 'currency',
                'annual_expected_loss_reduction' => 'currency/yr',
                'annual_net_benefit' => 'currency/yr',
                'npv_10_year' => 'currency',
                'simple_payback_years' => 'years',
                'insurance_non_renewal_flag' => '',
                'cal_fire_priority' => '',
                'compliance_status' => '',
            ],
        ];
    }
}
