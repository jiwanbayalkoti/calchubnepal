<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Cable Size Estimator
 */
class CableSizeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'cable_size_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('current', 'Load Current', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 32, 'unit' => 'A']),
            $this->field('length', 'Cable Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 30, 'unit' => 'm']),
            $this->field('voltage', 'System Voltage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 230, 'unit' => 'V']),
            $this->field('max_drop_percent', 'Max Voltage Drop', 'number', ['min' => 0, 'max' => 10, 'step' => 0.01, 'default' => 3, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // Simplified copper: A (mm²) ≈ (2 × L × I × 0.0175) / (V × drop%)
        $i = $this->requireNumeric($inputs, 'current');
        $l = $this->requireNumeric($inputs, 'length');
        $v = $this->requireNumeric($inputs, 'voltage');
        $drop = $this->requireNumeric($inputs, 'max_drop_percent');
        $area = (2 * $l * $i * 0.0175) / ($v * ($drop / 100));
        $standard = [1.5, 2.5, 4, 6, 10, 16, 25, 35, 50, 70, 95];
        $suggested = end($standard);
        foreach ($standard as $size) {
            if ($size >= $area) {
                $suggested = $size;
                break;
            }
        }
        return [
            'results' => [
                'minimum_area_mm2' => $this->round($area, 2),
                'suggested_cable_mm2' => $suggested,
            ],
            'breakdown' => ['note' => 'Approximate copper sizing — verify with local electrical codes'],
            'units' => ['minimum_area_mm2' => 'mm²', 'suggested_cable_mm2' => 'mm²'],
        ];
    }
}
