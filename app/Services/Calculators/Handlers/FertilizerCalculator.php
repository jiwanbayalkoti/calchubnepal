<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Fertilizer Calculator
 */
class FertilizerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'fertilizer_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('area', 'Area', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1, 'unit' => 'hectare']),
            $this->field('n_rate', 'Nitrogen Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100, 'unit' => 'kg/ha']),
            $this->field('p_rate', 'Phosphorus Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50, 'unit' => 'kg/ha']),
            $this->field('k_rate', 'Potassium Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 40, 'unit' => 'kg/ha']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'area');
        return [
            'results' => [
                'nitrogen_kg' => $this->round($area * $this->requireNumeric($inputs, 'n_rate'), 1),
                'phosphorus_kg' => $this->round($area * $this->requireNumeric($inputs, 'p_rate'), 1),
                'potassium_kg' => $this->round($area * $this->requireNumeric($inputs, 'k_rate'), 1),
            ],
            'breakdown' => ['area_ha' => $area],
            'units' => ['nitrogen_kg' => 'kg', 'phosphorus_kg' => 'kg', 'potassium_kg' => 'kg'],
        ];
    }
}
