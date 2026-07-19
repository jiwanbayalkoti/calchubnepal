<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Marble Calculator
 */
class MarbleCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'marble_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('area', 'Floor/Wall Area', 'number', ['min' => 0.1, 'max' => 1000000000, 'step' => 0.01, 'default' => 20, 'unit' => 'm²']),
            $this->field('tile_length', 'Marble Length', 'number', ['min' => 0.1, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.6, 'unit' => 'm']),
            $this->field('tile_width', 'Marble Width', 'number', ['min' => 0.1, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.6, 'unit' => 'm']),
            $this->field('wastage', 'Wastage', 'number', ['min' => 0, 'max' => 30, 'step' => 0.01, 'default' => 10, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'area');
        $tileArea = $this->requireNumeric($inputs, 'tile_length') * $this->requireNumeric($inputs, 'tile_width');
        $wastage = $this->requireNumeric($inputs, 'wastage');
        $needed = $this->safeDivide($area, $tileArea) * (1 + $wastage / 100);
        return [
            'results' => ['pieces_required' => (int) ceil($needed), 'marble_area_with_wastage' => $this->round($area * (1 + $wastage / 100), 2)],
            'breakdown' => ['piece_area' => $this->round($tileArea, 4)],
            'units' => ['pieces_required' => 'pcs', 'marble_area_with_wastage' => 'm²'],
        ];
    }
}
