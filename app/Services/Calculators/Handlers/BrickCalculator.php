<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates the number of bricks and mortar volume required to build a
 * wall of a given size, accounting for mortar joint thickness and wastage.
 */
class BrickCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'brick_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wall_length', 'Wall Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 10]),
            $this->field('wall_height', 'Wall Height', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 100, 'step' => 0.01, 'default' => 3]),
            $this->field('wall_thickness', 'Wall Thickness', 'number', ['unit' => 'm', 'min' => 0.05, 'max' => 2, 'step' => 0.01, 'default' => 0.23]),
            $this->field('brick_length', 'Brick Length', 'number', ['unit' => 'mm', 'min' => 50, 'max' => 500, 'step' => 1, 'default' => 190, 'required' => false]),
            $this->field('brick_width', 'Brick Width', 'number', ['unit' => 'mm', 'min' => 25, 'max' => 300, 'step' => 1, 'default' => 90, 'required' => false]),
            $this->field('brick_height', 'Brick Height', 'number', ['unit' => 'mm', 'min' => 25, 'max' => 300, 'step' => 1, 'default' => 90, 'required' => false]),
            $this->field('mortar_thickness', 'Mortar Joint Thickness', 'number', ['unit' => 'mm', 'min' => 0, 'max' => 30, 'step' => 1, 'default' => 10, 'required' => false]),
            $this->field('wastage_percent', 'Wastage', 'number', ['unit' => '%', 'min' => 0, 'max' => 30, 'step' => 0.5, 'default' => 5, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'wall_length');
        $height = $this->requireNumeric($inputs, 'wall_height');
        $thickness = $this->requireNumeric($inputs, 'wall_thickness');

        $brickLengthMm = $this->toFloat($inputs, 'brick_length', 190);
        $brickWidthMm = $this->toFloat($inputs, 'brick_width', 90);
        $brickHeightMm = $this->toFloat($inputs, 'brick_height', 90);
        $mortarMm = $this->toFloat($inputs, 'mortar_thickness', 10);
        $wastagePercent = $this->toFloat($inputs, 'wastage_percent', 5);

        $wallVolume = $length * $height * $thickness;

        $brickWithMortarVolumeM3 = (($brickLengthMm + $mortarMm) / 1000)
            * (($brickWidthMm + $mortarMm) / 1000)
            * (($brickHeightMm + $mortarMm) / 1000);

        $brickVolumeM3 = ($brickLengthMm / 1000) * ($brickWidthMm / 1000) * ($brickHeightMm / 1000);

        $baseBrickCount = $this->safeDivide($wallVolume, $brickWithMortarVolumeM3);
        $totalBricks = $baseBrickCount * (1 + $wastagePercent / 100);

        $totalBrickVolume = $baseBrickCount * $brickVolumeM3;
        $mortarVolume = max(0, $wallVolume - $totalBrickVolume);

        return [
            'results' => [
                'bricks_required' => (int) ceil($totalBricks),
                'mortar_volume' => $this->round($mortarVolume),
                'wall_volume' => $this->round($wallVolume),
            ],
            'breakdown' => [
                'wall_volume' => $this->round($wallVolume),
                'brick_volume_with_mortar' => $this->round($brickWithMortarVolumeM3, 6),
                'base_bricks_without_wastage' => (int) ceil($baseBrickCount),
                'wastage_percent' => $wastagePercent,
                'mortar_volume' => $this->round($mortarVolume),
            ],
            'units' => [
                'bricks_required' => 'bricks',
                'mortar_volume' => 'm³',
                'wall_volume' => 'm³',
            ],
        ];
    }
}
