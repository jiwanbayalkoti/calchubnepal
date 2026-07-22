<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Whole-Home Electrification Bundle ROI
 * Heat pump + induction + EV charger + solar + battery — bundled vs sequential 25-yr NPV.
 */
class WholeHomeElectrificationBundleRoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'whole_home_electrification_bundle_roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('heat_pump_cost', 'Heat Pump Install', 'number', ['min' => 0, 'max' => 40000, 'step' => 100, 'default' => 12000, 'unit' => 'currency']),
            $this->field('induction_cost', 'Induction Range Install', 'number', ['min' => 0, 'max' => 10000, 'step' => 50, 'default' => 2500, 'unit' => 'currency']),
            $this->field('ev_charger_cost', 'EV Charger Install', 'number', ['min' => 0, 'max' => 5000, 'step' => 50, 'default' => 1200, 'unit' => 'currency']),
            $this->field('solar_cost', 'Solar Install', 'number', ['min' => 0, 'max' => 80000, 'step' => 100, 'default' => 22000, 'unit' => 'currency']),
            $this->field('battery_cost', 'Battery Install', 'number', ['min' => 0, 'max' => 30000, 'step' => 100, 'default' => 12000, 'unit' => 'currency']),
            $this->field('panel_upgrade_cost', 'Electrical Panel Upgrade (if needed)', 'number', ['min' => 0, 'max' => 15000, 'step' => 100, 'default' => 3500, 'unit' => 'currency', 'required' => false]),
            $this->field('credit_25c', '§25C Efficiency Credits', 'number', ['min' => 0, 'max' => 10000, 'step' => 50, 'default' => 2000, 'unit' => 'currency', 'required' => false]),
            $this->field('credit_25d', '§25D Clean Energy Credits (0 for 2026+)', 'number', ['min' => 0, 'max' => 30000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('credit_30c', '§30C Charger Credit', 'number', ['min' => 0, 'max' => 1000, 'step' => 25, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('heehr_boost', 'HEEHRA / Low-Income Boost', 'number', ['min' => 0, 'max' => 20000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('annual_savings_bundled', 'Annual OpEx Savings (Bundled)', 'number', ['min' => 0, 'max' => 15000, 'step' => 50, 'default' => 3200, 'unit' => 'currency']),
            $this->field('annual_savings_sequential', 'Annual OpEx Savings (Sequential Avg)', 'number', ['min' => 0, 'max' => 15000, 'step' => 50, 'default' => 2400, 'unit' => 'currency']),
            $this->field('discount_rate', 'Discount Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('needs_panel_upgrade', 'Panel Upgrade Required?', 'select', [
                'options' => ['yes' => 'Yes — trigger upgrade', 'no' => 'No — existing panel OK'],
                'default' => 'yes',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $hp = $this->requireNumeric($inputs, 'heat_pump_cost');
        $ind = $this->requireNumeric($inputs, 'induction_cost');
        $evc = $this->requireNumeric($inputs, 'ev_charger_cost');
        $solar = $this->requireNumeric($inputs, 'solar_cost');
        $batt = $this->requireNumeric($inputs, 'battery_cost');
        $panel = $this->toFloat($inputs, 'panel_upgrade_cost', 3500);
        $c25c = $this->toFloat($inputs, 'credit_25c', 0);
        $c25d = $this->toFloat($inputs, 'credit_25d', 0);
        $c30c = $this->toFloat($inputs, 'credit_30c', 0);
        $heehr = $this->toFloat($inputs, 'heehr_boost', 0);
        $savB = $this->requireNumeric($inputs, 'annual_savings_bundled');
        $savS = $this->requireNumeric($inputs, 'annual_savings_sequential');
        $r = $this->toFloat($inputs, 'discount_rate', 5) / 100;
        $needPanel = $this->toString($inputs, 'needs_panel_upgrade', 'yes') === 'yes';

        $gross = $hp + $ind + $evc + $solar + $batt + ($needPanel ? $panel : 0);
        $credits = $c25c + $c25d + $c30c + $heehr;
        $netBundled = max(0, $gross - $credits);

        // Sequential: same measures but staggered — lose ~8% synergy + pay panel once anyway if needed
        $sequentialGross = $gross * 1.05; // contractor remobilization premium
        $netSequential = max(0, $sequentialGross - ($credits * 0.9)); // some credits harder to stack over years

        $npvB = -$netBundled;
        $npvS = -$netSequential;
        for ($y = 1; $y <= 25; $y++) {
            $npvB += $savB / ((1 + $r) ** $y);
            $npvS += $savS / ((1 + $r) ** $y);
        }

        $paybackB = $savB > 0 ? $netBundled / $savB : null;
        $advantage = $npvB - $npvS;

        return [
            'results' => [
                'bundled_net_cost' => $this->round($netBundled),
                'sequential_net_cost' => $this->round($netSequential),
                'bundled_25yr_npv' => $this->round($npvB),
                'sequential_25yr_npv' => $this->round($npvS),
                'bundle_advantage_npv' => $this->round($advantage),
                'bundled_simple_payback_years' => $paybackB === null ? 'n/a' : $this->round($paybackB, 1),
                'panel_upgrade_triggered' => $needPanel ? 'Yes' : 'No',
                'total_credits_applied' => $this->round($credits),
                'recommendation' => $advantage > 0
                    ? 'Bundle now — higher 25-yr NPV than sequencing'
                    : 'Sequencing may be fine — re-check incentives and cash flow',
            ],
            'breakdown' => [
                'gross_project_cost' => $this->round($gross),
                'credits_25c_25d_30c_heehr' => $this->round($credits),
                'note' => '§25D defaults to $0 for 2026+ installs; enter state/utility incentives in the credit fields if applicable.',
                'formula' => 'NPV = −net cost + Σ annual savings / (1+r)^t for t=1..25',
            ],
            'units' => [
                'bundled_net_cost' => 'currency',
                'sequential_net_cost' => 'currency',
                'bundled_25yr_npv' => 'currency',
                'sequential_25yr_npv' => 'currency',
                'bundle_advantage_npv' => 'currency',
                'bundled_simple_payback_years' => 'years',
                'panel_upgrade_triggered' => '',
                'total_credits_applied' => 'currency',
                'recommendation' => '',
            ],
        ];
    }
}
