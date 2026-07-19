<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates cement and sand quantities required to plaster a wall area
 * to a given thickness, using a 1.33 dry-volume factor typical for
 * plaster work (accounts for bulking and wastage).
 */
class PlasterCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'plaster_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wall_area', 'Wall Area', 'number', ['unit' => 'm²', 'min' => 0.1, 'max' => 100000, 'step' => 0.1, 'default' => 100]),
            $this->field('thickness', 'Plaster Thickness', 'number', ['unit' => 'mm', 'min' => 5, 'max' => 50, 'step' => 1, 'default' => 12]),
            $this->field('cement_ratio', 'Cement Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 1, 'required' => false]),
            $this->field('sand_ratio', 'Sand Ratio Part', 'number', ['min' => 1, 'max' => 10, 'step' => 0.5, 'default' => 6, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wallArea = $this->requireNumeric($inputs, 'wall_area');
        $thicknessMm = $this->requireNumeric($inputs, 'thickness');
        $cementRatio = $this->toFloat($inputs, 'cement_ratio', 1);
        $sandRatio = $this->toFloat($inputs, 'sand_ratio', 6);

        $wetVolume = $wallArea * ($thicknessMm / 1000);
        $dryVolume = $wetVolume * 1.33;
        $totalRatio = $cementRatio + $sandRatio;

        $cementVolume = $this->safeDivide($dryVolume * $cementRatio, $totalRatio);
        $sandVolume = $this->safeDivide($dryVolume * $sandRatio, $totalRatio);

        $cementWeightKg = $cementVolume * 1440;
        $cementBags = $cementWeightKg / 50;

        return [
            'results' => [
                'cement_bags' => (int) ceil($cementBags),
                'sand_volume' => $this->round($sandVolume),
                'wet_volume' => $this->round($wetVolume),
            ],
            'breakdown' => [
                'dry_volume' => $this->round($dryVolume),
                'cement_volume' => $this->round($cementVolume, 4),
                'cement_weight' => $this->round($cementWeightKg),
            ],
            'units' => [
                'cement_bags' => 'bags (50kg)',
                'sand_volume' => 'm³',
                'wet_volume' => 'm³',
            ],
        ];
    }
}
