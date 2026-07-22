<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Home Insulation ROI Calculator
 * Heat-loss reduction from R-value upgrade → bill savings, payback, 20-yr NPV-ish savings, IRR.
 */
class HomeInsulationRoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'home_insulation_roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('home_sqft', 'Home Floor Area', 'number', ['min' => 400, 'max' => 10000, 'step' => 50, 'default' => 1800, 'unit' => 'sq.ft']),
            $this->field('r_current', 'Current Insulation R-Value', 'number', ['min' => 1, 'max' => 60, 'step' => 1, 'default' => 13, 'unit' => 'R']),
            $this->field('r_target', 'Target R-Value', 'number', ['min' => 2, 'max' => 80, 'step' => 1, 'default' => 38, 'unit' => 'R']),
            $this->field('climate_zone', 'Climate Zone', 'select', [
                'options' => [
                    '1_2' => 'IECC 1–2 (hot)',
                    '3_4' => 'IECC 3–4 (mixed)',
                    '5_6' => 'IECC 5–6 (cold)',
                    '7_8' => 'IECC 7–8 (very cold)',
                ],
                'default' => '5_6',
            ]),
            $this->field('annual_hvac_bill', 'Annual Heating + Cooling Bill', 'number', ['min' => 200, 'max' => 20000, 'step' => 50, 'default' => 2400, 'unit' => 'currency']),
            $this->field('install_cost', 'Insulation Install Cost', 'number', ['min' => 200, 'max' => 30000, 'step' => 50, 'default' => 3500, 'unit' => 'currency']),
            $this->field('tax_credit', '§25C Credit (up to $1,200/yr)', 'number', ['min' => 0, 'max' => 1200, 'step' => 50, 'default' => 600, 'unit' => 'currency', 'required' => false]),
            $this->field('fuel_growth', 'Fuel / Power Price Growth', 'number', ['min' => 0, 'max' => 12, 'step' => 0.5, 'default' => 3, 'unit' => '%/yr', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $sqft = $this->requireNumeric($inputs, 'home_sqft');
        $rCur = max(1, $this->requireNumeric($inputs, 'r_current'));
        $rTgt = max($rCur + 0.1, $this->requireNumeric($inputs, 'r_target'));
        $zone = $this->toString($inputs, 'climate_zone', '5_6');
        $bill = $this->requireNumeric($inputs, 'annual_hvac_bill');
        $install = $this->requireNumeric($inputs, 'install_cost');
        $credit = $this->toFloat($inputs, 'tax_credit', 600);
        $growth = $this->toFloat($inputs, 'fuel_growth', 3) / 100;

        $lossReduction = 1 - ($rCur / $rTgt);
        // Envelope share of HVAC bill ~55–70% by climate
        $envelopeShare = match ($zone) {
            '1_2' => 0.5,
            '3_4' => 0.55,
            '7_8' => 0.7,
            default => 0.6,
        };
        $annualSave = $bill * $envelopeShare * $lossReduction;
        $netCost = max(0, $install - $credit);
        $payback = $annualSave > 0 ? $netCost / $annualSave : null;

        $lifetime = 0.0;
        $cashflows = [-$netCost];
        for ($y = 1; $y <= 20; $y++) {
            $s = $annualSave * ((1 + $growth) ** ($y - 1));
            $lifetime += $s;
            $cashflows[] = $s;
        }
        $irr = $this->irr($cashflows);

        $ieccTarget = match ($zone) {
            '1_2' => 30,
            '3_4' => 38,
            '7_8' => 60,
            default => 49,
        };
        $hint = $rTgt < $ieccTarget
            ? sprintf('Target R-%.0f is below IECC 2021-ish attic target ~R-%.0f for this zone — another step-up still leaves savings on the table.', $rTgt, $ieccTarget)
            : 'Target meets or exceeds typical IECC attic R-value guidance for this zone.';

        return [
            'results' => [
                'heat_loss_reduction_pct' => $this->round($lossReduction * 100, 1),
                'annual_bill_savings' => $this->round($annualSave),
                'payback_years' => $payback === null ? 'n/a' : $this->round($payback, 1),
                'lifetime_savings_20yr' => $this->round($lifetime),
                'net_20yr_gain' => $this->round($lifetime - $netCost),
                'irr_pct' => $irr === null ? 'n/a' : $this->round($irr * 100, 1),
                'tier_progression_hint' => $hint,
            ],
            'breakdown' => [
                'net_cost_after_credit' => $this->round($netCost),
                'envelope_share_assumed' => $envelopeShare,
                'iecc_reference_r' => $ieccTarget,
                'anchors' => 'DOE Building America / IECC 2021 R targets, LBNL envelope retrofit studies, IRS §25C',
            ],
            'units' => [
                'heat_loss_reduction_pct' => '%',
                'annual_bill_savings' => 'currency/yr',
                'payback_years' => 'years',
                'lifetime_savings_20yr' => 'currency',
                'net_20yr_gain' => 'currency',
                'irr_pct' => '%',
                'tier_progression_hint' => '',
            ],
        ];
    }

    /** @param array<int, float> $cashflows */
    protected function irr(array $cashflows): ?float
    {
        $rate = 0.1;
        for ($i = 0; $i < 40; $i++) {
            $npv = 0.0;
            $d = 0.0;
            foreach ($cashflows as $t => $cf) {
                $npv += $cf / ((1 + $rate) ** $t);
                if ($t > 0) {
                    $d -= $t * $cf / ((1 + $rate) ** ($t + 1));
                }
            }
            if (abs($d) < 1e-9) {
                break;
            }
            $new = $rate - $npv / $d;
            if (! is_finite($new) || $new <= -0.99) {
                return null;
            }
            if (abs($new - $rate) < 1e-7) {
                return $new;
            }
            $rate = $new;
        }

        return is_finite($rate) ? $rate : null;
    }
}
