<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * AC Sizing + Cost Calculator
 * Manual J square-foot approximation + SEER 14/17/22 10-yr TCO comparison.
 */
class AcSizeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ac_size_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('room_sqft', 'Room / Conditioned Area', 'number', ['min' => 50, 'max' => 5000, 'step' => 10, 'default' => 400, 'unit' => 'sq.ft']),
            $this->field('ceiling_height', 'Ceiling Height', 'number', ['min' => 7, 'max' => 16, 'step' => 0.5, 'default' => 8, 'unit' => 'ft', 'required' => false]),
            $this->field('climate_zone', 'Climate Zone', 'select', [
                'options' => [
                    '1_2' => 'Hot (IECC 1–2)',
                    '3_4' => 'Mixed (IECC 3–4)',
                    '5_6' => 'Cool (IECC 5–6)',
                    '7_8' => 'Cold (IECC 7–8)',
                ],
                'default' => '3_4',
            ]),
            $this->field('sun_exposure', 'Sun Exposure', 'select', [
                'options' => ['low' => 'Shaded / low', 'medium' => 'Average', 'high' => 'High / west-facing'],
                'default' => 'medium',
            ]),
            $this->field('occupants', 'Occupants', 'number', ['min' => 1, 'max' => 20, 'step' => 1, 'default' => 3]),
            $this->field('insulation', 'Insulation Rating', 'select', [
                'options' => ['poor' => 'Poor', 'average' => 'Average', 'good' => 'Good / tight'],
                'default' => 'average',
            ]),
            $this->field('electricity_rate', 'Electricity Rate', 'number', ['min' => 0.05, 'max' => 0.6, 'step' => 0.01, 'default' => 0.14, 'unit' => 'currency/kWh']),
            $this->field('cooling_hours', 'Equivalent Full-Load Cooling Hours / Yr', 'number', ['min' => 200, 'max' => 3000, 'step' => 50, 'default' => 1000, 'unit' => 'hrs', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $sqft = $this->requireNumeric($inputs, 'room_sqft');
        $height = $this->toFloat($inputs, 'ceiling_height', 8);
        $zone = $this->toString($inputs, 'climate_zone', '3_4');
        $sun = $this->toString($inputs, 'sun_exposure', 'medium');
        $people = $this->requireNumeric($inputs, 'occupants');
        $insul = $this->toString($inputs, 'insulation', 'average');
        $rate = $this->requireNumeric($inputs, 'electricity_rate');
        $eflh = $this->toFloat($inputs, 'cooling_hours', 1000);

        $btuPerSqft = match ($zone) {
            '1_2' => 30,
            '5_6' => 20,
            '7_8' => 18,
            default => 25,
        };
        $sunFactor = match ($sun) {
            'low' => 0.9,
            'high' => 1.15,
            default => 1.0,
        };
        $insFactor = match ($insul) {
            'poor' => 1.15,
            'good' => 0.9,
            default => 1.0,
        };
        $heightFactor = $height / 8;

        $btu = ($sqft * $btuPerSqft + $people * 600) * $sunFactor * $insFactor * $heightFactor;
        $tonsRaw = $btu / 12000;
        $tons = ceil($tonsRaw * 2) / 2; // nearest 0.5 ton up
        $tons = max(0.5, $tons);
        $sizedBtu = $tons * 12000;

        $tiers = [
            14 => ['install' => 3500 + $tons * 1200, 'label' => 'SEER 14'],
            17 => ['install' => 4200 + $tons * 1400, 'label' => 'SEER 17'],
            22 => ['install' => 5200 + $tons * 1700, 'label' => 'SEER 22'],
        ];

        $comparisons = [];
        $bestSeer = 14;
        $bestTco = PHP_FLOAT_MAX;
        foreach ($tiers as $seer => $meta) {
            // kWh ≈ (BTU/hr × hours) / (SEER × 1000)
            $annualKwh = ($sizedBtu * $eflh) / ($seer * 1000);
            $op10 = $annualKwh * $rate * 10;
            $tco = $meta['install'] + $op10;
            $comparisons[$seer] = [
                'install' => $meta['install'],
                'op10' => $op10,
                'tco' => $tco,
            ];
            if ($tco < $bestTco) {
                $bestTco = $tco;
                $bestSeer = $seer;
            }
        }

        $marginal = ($comparisons[22]['tco'] - $comparisons[17]['tco']);
        $seer22ExtraInstall = $comparisons[22]['install'] - $comparisons[17]['install'];
        $seer22OpSaveYr = ($comparisons[17]['op10'] - $comparisons[22]['op10']) / 10;
        $marginalPayback = $seer22OpSaveYr > 0 ? $seer22ExtraInstall / $seer22OpSaveYr : null;

        return [
            'results' => [
                'recommended_btu_hr' => $this->round($sizedBtu),
                'recommended_tons' => $this->round($tons, 1),
                'seer14_10yr_tco' => $this->round($comparisons[14]['tco']),
                'seer17_10yr_tco' => $this->round($comparisons[17]['tco']),
                'seer22_10yr_tco' => $this->round($comparisons[22]['tco']),
                'recommended_seer_tier' => 'SEER '.$bestSeer,
                'seer22_vs_17_marginal_payback_years' => $marginalPayback === null ? 'n/a' : $this->round($marginalPayback, 1),
            ],
            'breakdown' => [
                'raw_btu_before_round' => $this->round($btu),
                'seer14_install' => $this->round($comparisons[14]['install']),
                'seer17_install' => $this->round($comparisons[17]['install']),
                'seer22_install' => $this->round($comparisons[22]['install']),
                'tco_delta_22_vs_17' => $this->round($marginal),
                'anchors' => 'ACCA Manual J 8th Ed sq-ft method approximation, DOE SEER2 tiers, EIA CDD-style EFLH',
            ],
            'units' => [
                'recommended_btu_hr' => 'BTU/h',
                'recommended_tons' => 'tons',
                'seer14_10yr_tco' => 'currency',
                'seer17_10yr_tco' => 'currency',
                'seer22_10yr_tco' => 'currency',
                'recommended_seer_tier' => '',
                'seer22_vs_17_marginal_payback_years' => 'years',
            ],
        ];
    }
}
