<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Crop Yield Calculator
 */
class CropYieldCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'crop_yield_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('total_production', 'Total Production', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000, 'unit' => 'kg']),
            $this->field('area', 'Area', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'unit' => 'hectare']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $yield = $this->safeDivide($this->requireNumeric($inputs, 'total_production'), $this->requireNumeric($inputs, 'area'));
        return [
            'results' => ['yield_kg_per_ha' => $this->round($yield, 2), 'yield_tons_per_ha' => $this->round($yield / 1000, 3)],
            'breakdown' => [],
            'units' => ['yield_kg_per_ha' => 'kg/ha', 'yield_tons_per_ha' => 't/ha'],
        ];
    }
}
