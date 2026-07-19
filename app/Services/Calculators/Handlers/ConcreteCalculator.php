<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Calculates concrete volume for a slab/footing/column pour and the
 * resulting cement, sand, aggregate and water quantities.
 */
class ConcreteCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'concrete_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 5]),
            $this->field('width', 'Width', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 4]),
            $this->field('thickness', 'Thickness', 'number', ['unit' => 'm', 'min' => 0.01, 'max' => 5, 'step' => 0.01, 'default' => 0.15]),
            $this->field('cement_ratio', 'Cement Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 1, 'required' => false]),
            $this->field('sand_ratio', 'Sand Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 2, 'required' => false]),
            $this->field('aggregate_ratio', 'Aggregate Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 4, 'required' => false]),
            $this->field('water_cement_ratio', 'Water-Cement Ratio', 'number', ['min' => 0.3, 'max' => 0.7, 'step' => 0.01, 'default' => 0.45, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $width = $this->requireNumeric($inputs, 'width');
        $thickness = $this->requireNumeric($inputs, 'thickness');

        $cementRatio = $this->toFloat($inputs, 'cement_ratio', 1);
        $sandRatio = $this->toFloat($inputs, 'sand_ratio', 2);
        $aggregateRatio = $this->toFloat($inputs, 'aggregate_ratio', 4);
        $waterCementRatio = $this->toFloat($inputs, 'water_cement_ratio', 0.45);

        $wetVolume = $length * $width * $thickness;
        $dryVolume = $wetVolume * 1.54;
        $totalRatio = $cementRatio + $sandRatio + $aggregateRatio;

        $cementVolume = $this->safeDivide($dryVolume * $cementRatio, $totalRatio);
        $sandVolume = $this->safeDivide($dryVolume * $sandRatio, $totalRatio);
        $aggregateVolume = $this->safeDivide($dryVolume * $aggregateRatio, $totalRatio);

        $cementWeightKg = $cementVolume * 1440;
        $cementBags = $cementWeightKg / 50;
        $sandWeightKg = $sandVolume * 1600;
        $aggregateWeightKg = $aggregateVolume * 1550;
        $waterLiters = $cementWeightKg * $waterCementRatio;

        return [
            'results' => [
                'concrete_volume' => $this->round($wetVolume),
                'cement_bags' => (int) ceil($cementBags),
                'sand_volume' => $this->round($sandVolume),
                'aggregate_volume' => $this->round($aggregateVolume),
                'water_required' => $this->round($waterLiters),
            ],
            'breakdown' => [
                'wet_volume' => $this->round($wetVolume),
                'dry_volume' => $this->round($dryVolume),
                'cement_weight' => $this->round($cementWeightKg),
                'sand_weight' => $this->round($sandWeightKg),
                'aggregate_weight' => $this->round($aggregateWeightKg),
            ],
            'units' => [
                'concrete_volume' => 'm³',
                'cement_bags' => 'bags (50kg)',
                'sand_volume' => 'm³',
                'aggregate_volume' => 'm³',
                'water_required' => 'liters',
            ],
        ];
    }
}
