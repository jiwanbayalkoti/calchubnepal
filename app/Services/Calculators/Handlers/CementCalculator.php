<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates the number of cement bags required for a concrete/mortar mix
 * given its wet volume and mix ratio (cement:sand:aggregate).
 */
class CementCalculator extends AbstractCalculatorHandler
{
    public const CEMENT_DENSITY_KG_PER_M3 = 1440;

    public const BAG_WEIGHT_KG = 50;

    public function key(): string
    {
        return 'cement_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wet_volume', 'Wet Volume', 'number', ['unit' => 'm³', 'min' => 0.01, 'max' => 10000, 'step' => 0.01, 'default' => 1]),
            $this->field('cement_ratio', 'Cement Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 1]),
            $this->field('sand_ratio', 'Sand Ratio Part', 'number', ['min' => 0, 'max' => 20, 'step' => 0.5, 'default' => 2]),
            $this->field('aggregate_ratio', 'Aggregate Ratio Part', 'number', ['min' => 0, 'max' => 20, 'step' => 0.5, 'default' => 4, 'required' => false]),
            $this->field('dry_volume_factor', 'Dry Volume Factor', 'number', ['min' => 1, 'max' => 2, 'step' => 0.01, 'default' => 1.54, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wetVolume = $this->requireNumeric($inputs, 'wet_volume');
        $cementRatio = $this->requireNumeric($inputs, 'cement_ratio');
        $sandRatio = $this->toFloat($inputs, 'sand_ratio', 2);
        $aggregateRatio = $this->toFloat($inputs, 'aggregate_ratio', 0);
        $dryVolumeFactor = $this->toFloat($inputs, 'dry_volume_factor', 1.54);

        $totalRatio = $cementRatio + $sandRatio + $aggregateRatio;
        $dryVolume = $wetVolume * $dryVolumeFactor;

        $cementVolume = $this->safeDivide($dryVolume * $cementRatio, $totalRatio);
        $sandVolume = $this->safeDivide($dryVolume * $sandRatio, $totalRatio);
        $aggregateVolume = $this->safeDivide($dryVolume * $aggregateRatio, $totalRatio);

        $cementWeightKg = $cementVolume * self::CEMENT_DENSITY_KG_PER_M3;
        $cementBags = $cementWeightKg / self::BAG_WEIGHT_KG;

        return [
            'results' => [
                'cement_bags' => (int) ceil($cementBags),
                'cement_weight' => $this->round($cementWeightKg),
                'sand_volume' => $this->round($sandVolume),
                'aggregate_volume' => $this->round($aggregateVolume),
            ],
            'breakdown' => [
                'dry_volume' => $this->round($dryVolume),
                'total_ratio_parts' => $totalRatio,
                'cement_volume' => $this->round($cementVolume, 4),
                'cement_bags_exact' => $this->round($cementBags, 2),
            ],
            'units' => [
                'cement_bags' => 'bags (50kg)',
                'cement_weight' => 'kg',
                'sand_volume' => 'm³',
                'aggregate_volume' => 'm³',
            ],
        ];
    }
}
