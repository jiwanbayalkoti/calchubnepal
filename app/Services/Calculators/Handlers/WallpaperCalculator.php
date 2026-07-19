<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Wallpaper Calculator
 */
class WallpaperCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'wallpaper_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wall_area', 'Wall Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 40, 'unit' => 'm²']),
            $this->field('roll_coverage', 'Coverage Per Roll', 'number', ['min' => 0.5, 'max' => 1000000000, 'step' => 0.01, 'default' => 5, 'unit' => 'm²']),
            $this->field('wastage', 'Wastage', 'number', ['min' => 0, 'max' => 30, 'step' => 0.01, 'default' => 15, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'wall_area') * (1 + $this->requireNumeric($inputs, 'wastage') / 100);
        $coverage = $this->requireNumeric($inputs, 'roll_coverage');
        $rolls = ceil($this->safeDivide($area, $coverage));
        return [
            'results' => ['rolls_needed' => (int) $rolls, 'adjusted_area' => $this->round($area, 2)],
            'breakdown' => ['coverage_per_roll' => $coverage],
            'units' => ['rolls_needed' => 'rolls', 'adjusted_area' => 'm²'],
        ];
    }
}
