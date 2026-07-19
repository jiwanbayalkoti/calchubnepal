<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Flooring Calculator
 */
class FlooringCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'flooring_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Room Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 4, 'unit' => 'm']),
            $this->field('width', 'Room Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 3.5, 'unit' => 'm']),
            $this->field('price_per_m2', 'Price Per m²', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 800, 'unit' => 'currency']),
            $this->field('wastage', 'Wastage', 'number', ['min' => 0, 'max' => 20, 'step' => 0.01, 'default' => 5, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        $withWaste = $area * (1 + $this->requireNumeric($inputs, 'wastage') / 100);
        $cost = $withWaste * $this->requireNumeric($inputs, 'price_per_m2');
        return [
            'results' => [
                'floor_area' => $this->round($area, 2),
                'material_area' => $this->round($withWaste, 2),
                'estimated_cost' => $this->round($cost),
            ],
            'breakdown' => ['wastage_percent' => $this->requireNumeric($inputs, 'wastage')],
            'units' => ['floor_area' => 'm²', 'material_area' => 'm²', 'estimated_cost' => 'currency'],
        ];
    }
}
