<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates the sand volume/weight required for a concrete or mortar mix
 * given its wet volume and mix ratio.
 */
class SandCalculator extends AbstractCalculatorHandler
{
    public const SAND_DENSITY_KG_PER_M3 = 1600;

    public function key(): string
    {
        return 'sand_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wet_volume', 'Wet Volume', 'number', ['unit' => 'm³', 'min' => 0.01, 'max' => 10000, 'step' => 0.01, 'default' => 1]),
            $this->field('cement_ratio', 'Cement Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 1]),
            $this->field('sand_ratio', 'Sand Ratio Part', 'number', ['min' => 1, 'max' => 20, 'step' => 0.5, 'default' => 2]),
            $this->field('aggregate_ratio', 'Aggregate Ratio Part', 'number', ['min' => 0, 'max' => 20, 'step' => 0.5, 'default' => 4, 'required' => false]),
            $this->field('dry_volume_factor', 'Dry Volume Factor', 'number', ['min' => 1, 'max' => 2, 'step' => 0.01, 'default' => 1.54, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wetVolume = $this->requireNumeric($inputs, 'wet_volume');
        $cementRatio = $this->requireNumeric($inputs, 'cement_ratio');
        $sandRatio = $this->requireNumeric($inputs, 'sand_ratio');
        $aggregateRatio = $this->toFloat($inputs, 'aggregate_ratio', 0);
        $dryVolumeFactor = $this->toFloat($inputs, 'dry_volume_factor', 1.54);

        $totalRatio = $cementRatio + $sandRatio + $aggregateRatio;
        $dryVolume = $wetVolume * $dryVolumeFactor;

        $sandVolume = $this->safeDivide($dryVolume * $sandRatio, $totalRatio);
        $sandWeightKg = $sandVolume * self::SAND_DENSITY_KG_PER_M3;
        $sandTons = $sandWeightKg / 1000;

        return [
            'results' => [
                'sand_volume' => $this->round($sandVolume),
                'sand_weight' => $this->round($sandWeightKg),
                'sand_tons' => $this->round($sandTons, 3),
            ],
            'breakdown' => [
                'dry_volume' => $this->round($dryVolume),
                'total_ratio_parts' => $totalRatio,
            ],
            'units' => [
                'sand_volume' => 'm³',
                'sand_weight' => 'kg',
                'sand_tons' => 'tons',
            ],
        ];
    }
}
