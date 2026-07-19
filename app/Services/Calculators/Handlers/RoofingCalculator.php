<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Roofing Calculator
 */
class RoofingCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'roofing_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Roof Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'm']),
            $this->field('width', 'Roof Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 8, 'unit' => 'm']),
            $this->field('pitch_factor', 'Pitch Factor', 'number', ['min' => 1, 'max' => 2, 'step' => 0.01, 'default' => 1.15]),
            $this->field('sheet_coverage', 'Sheet Coverage', 'number', ['min' => 0.5, 'max' => 1000000000, 'step' => 0.01, 'default' => 1.8, 'unit' => 'm²']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $plan = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        $area = $plan * $this->requireNumeric($inputs, 'pitch_factor');
        $sheets = ceil($this->safeDivide($area, $this->requireNumeric($inputs, 'sheet_coverage')));
        return [
            'results' => ['roof_area' => $this->round($area, 2), 'sheets_needed' => (int) $sheets],
            'breakdown' => ['plan_area' => $this->round($plan, 2)],
            'units' => ['roof_area' => 'm²', 'sheets_needed' => 'sheets'],
        ];
    }
}
