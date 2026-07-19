<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Calculates the number of floor/wall tiles needed to cover a room area,
 * including a configurable wastage allowance.
 */
class TileCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tile_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('room_length', 'Room Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 5]),
            $this->field('room_width', 'Room Width', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 4]),
            $this->field('tile_length', 'Tile Length', 'number', ['unit' => 'cm', 'min' => 5, 'max' => 300, 'step' => 0.5, 'default' => 60]),
            $this->field('tile_width', 'Tile Width', 'number', ['unit' => 'cm', 'min' => 5, 'max' => 300, 'step' => 0.5, 'default' => 60]),
            $this->field('tiles_per_box', 'Tiles per Box', 'number', ['min' => 1, 'max' => 1000, 'step' => 1, 'default' => 4, 'required' => false]),
            $this->field('wastage_percent', 'Wastage', 'number', ['unit' => '%', 'min' => 0, 'max' => 30, 'step' => 0.5, 'default' => 10, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $roomLength = $this->requireNumeric($inputs, 'room_length');
        $roomWidth = $this->requireNumeric($inputs, 'room_width');
        $tileLengthCm = $this->requireNumeric($inputs, 'tile_length');
        $tileWidthCm = $this->requireNumeric($inputs, 'tile_width');
        $tilesPerBox = $this->toFloat($inputs, 'tiles_per_box', 0);
        $wastagePercent = $this->toFloat($inputs, 'wastage_percent', 10);

        $roomArea = $roomLength * $roomWidth;
        $tileArea = ($tileLengthCm / 100) * ($tileWidthCm / 100);

        $baseTiles = $this->safeDivide($roomArea, $tileArea);
        $totalTiles = $baseTiles * (1 + $wastagePercent / 100);
        $tilesNeeded = (int) ceil($totalTiles);

        $boxesNeeded = $tilesPerBox > 0 ? (int) ceil($tilesNeeded / $tilesPerBox) : null;

        return [
            'results' => array_filter([
                'tiles_required' => $tilesNeeded,
                'room_area' => $this->round($roomArea),
                'boxes_required' => $boxesNeeded,
            ], fn ($value) => $value !== null),
            'breakdown' => [
                'tile_area' => $this->round($tileArea, 4),
                'base_tiles_without_wastage' => (int) ceil($baseTiles),
                'wastage_percent' => $wastagePercent,
            ],
            'units' => [
                'tiles_required' => 'tiles',
                'room_area' => 'm²',
                'boxes_required' => 'boxes',
            ],
        ];
    }
}
