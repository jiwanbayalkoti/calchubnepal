<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates the coarse aggregate volume/weight required for a concrete
 * mix given its wet volume and mix ratio.
 */
class AggregateCalculator extends AbstractCalculatorHandler
{
    public const AGGREGATE_DENSITY_KG_PER_M3 = 1550;

    public function key(): string
    {
        return 'aggregate_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wet_volume', 'Wet Volume', 'number', ['unit' => 'm³', 'min' => 0.01, 'max' => 10000, 'step' => 0.01, 'default' => 1]),
            $this->field('cement_ratio', 'Cement Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 1]),
            $this->field('sand_ratio', 'Sand Ratio Part', 'number', ['min' => 1, 'max' => 20, 'step' => 0.5, 'default' => 2]),
            $this->field('aggregate_ratio', 'Aggregate Ratio Part', 'number', ['min' => 1, 'max' => 20, 'step' => 0.5, 'default' => 4]),
            $this->field('dry_volume_factor', 'Dry Volume Factor', 'number', ['min' => 1, 'max' => 2, 'step' => 0.01, 'default' => 1.54, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wetVolume = $this->requireNumeric($inputs, 'wet_volume');
        $cementRatio = $this->requireNumeric($inputs, 'cement_ratio');
        $sandRatio = $this->requireNumeric($inputs, 'sand_ratio');
        $aggregateRatio = $this->requireNumeric($inputs, 'aggregate_ratio');
        $dryVolumeFactor = $this->toFloat($inputs, 'dry_volume_factor', 1.54);

        $totalRatio = $cementRatio + $sandRatio + $aggregateRatio;
        $dryVolume = $wetVolume * $dryVolumeFactor;

        $aggregateVolume = $this->safeDivide($dryVolume * $aggregateRatio, $totalRatio);
        $aggregateWeightKg = $aggregateVolume * self::AGGREGATE_DENSITY_KG_PER_M3;
        $aggregateTons = $aggregateWeightKg / 1000;

        return [
            'results' => [
                'aggregate_volume' => $this->round($aggregateVolume),
                'aggregate_weight' => $this->round($aggregateWeightKg),
                'aggregate_tons' => $this->round($aggregateTons, 3),
            ],
            'breakdown' => [
                'dry_volume' => $this->round($dryVolume),
                'total_ratio_parts' => $totalRatio,
            ],
            'units' => [
                'aggregate_volume' => 'm³',
                'aggregate_weight' => 'kg',
                'aggregate_tons' => 'tons',
            ],
        ];
    }
}
