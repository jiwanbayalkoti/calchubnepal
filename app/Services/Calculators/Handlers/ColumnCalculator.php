<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Axial load carrying capacity of a short, axially loaded RCC column
 * using the simplified IS 456 approach:
 * Pu = 0.4 * fck * Ac + 0.67 * fy * Asc
 * where Ac is the net concrete area (gross area minus steel area) and
 * Asc is the longitudinal steel area.
 */
class ColumnCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'column_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('column_width', 'Column Width', 'number', ['unit' => 'mm', 'min' => 100, 'max' => 3000, 'step' => 1, 'default' => 300]),
            $this->field('column_depth', 'Column Depth', 'number', ['unit' => 'mm', 'min' => 100, 'max' => 3000, 'step' => 1, 'default' => 450]),
            $this->field('concrete_grade', 'Concrete Grade (fck)', 'number', ['unit' => 'N/mm²', 'min' => 15, 'max' => 80, 'step' => 1, 'default' => 20, 'required' => false]),
            $this->field('steel_grade', 'Steel Grade (fy)', 'number', ['unit' => 'N/mm²', 'min' => 250, 'max' => 600, 'step' => 1, 'default' => 415, 'required' => false]),
            $this->field('steel_percent', 'Longitudinal Steel', 'number', ['unit' => '%', 'min' => 0.8, 'max' => 6, 'step' => 0.1, 'default' => 1, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $width = $this->requireNumeric($inputs, 'column_width');
        $depth = $this->requireNumeric($inputs, 'column_depth');
        $fck = $this->toFloat($inputs, 'concrete_grade', 20);
        $fy = $this->toFloat($inputs, 'steel_grade', 415);
        $steelPercent = $this->toFloat($inputs, 'steel_percent', 1);

        $grossArea = $width * $depth;
        $steelArea = $grossArea * ($steelPercent / 100);
        $netConcreteArea = $grossArea - $steelArea;

        $axialCapacityN = (0.4 * $fck * $netConcreteArea) + (0.67 * $fy * $steelArea);
        $axialCapacityKn = $axialCapacityN / 1000;

        return [
            'results' => [
                'axial_load_capacity' => $this->round($axialCapacityKn),
                'gross_area' => $this->round($grossArea),
                'steel_area' => $this->round($steelArea),
            ],
            'breakdown' => [
                'net_concrete_area' => $this->round($netConcreteArea),
                'fck' => $fck,
                'fy' => $fy,
            ],
            'units' => [
                'axial_load_capacity' => 'kN',
                'gross_area' => 'mm²',
                'steel_area' => 'mm²',
            ],
        ];
    }
}
