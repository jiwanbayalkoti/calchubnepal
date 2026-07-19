<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Estimates the quantity of paint (in liters) required for a wall area,
 * accounting for door/window deductions, coverage rate and coat count.
 */
class PaintCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'paint_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wall_area', 'Total Wall Area', 'number', ['unit' => 'm²', 'min' => 0.1, 'max' => 100000, 'step' => 0.1, 'default' => 100]),
            $this->field('door_window_area', 'Door/Window Area', 'number', ['unit' => 'm²', 'min' => 0, 'max' => 10000, 'step' => 0.1, 'default' => 0, 'required' => false]),
            $this->field('coverage_per_liter', 'Coverage per Liter', 'number', ['unit' => 'm²/L', 'min' => 1, 'max' => 30, 'step' => 0.1, 'default' => 10, 'required' => false]),
            $this->field('number_of_coats', 'Number of Coats', 'integer', ['min' => 1, 'max' => 5, 'step' => 1, 'default' => 2, 'required' => false]),
            $this->field('price_per_liter', 'Price per Liter', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wallArea = $this->requireNumeric($inputs, 'wall_area');
        $deductionArea = $this->toFloat($inputs, 'door_window_area', 0);
        $coverage = $this->toFloat($inputs, 'coverage_per_liter', 10);
        $coats = $this->toInt($inputs, 'number_of_coats', 2);
        $pricePerLiter = $this->toFloat($inputs, 'price_per_liter', 0);

        $paintableArea = max(0, $wallArea - $deductionArea);
        $litersRequired = $this->safeDivide($paintableArea * $coats, $coverage);
        $estimatedCost = $litersRequired * $pricePerLiter;

        return [
            'results' => [
                'paint_required' => $this->round($litersRequired),
                'paintable_area' => $this->round($paintableArea),
                'estimated_cost' => $this->round($estimatedCost),
            ],
            'breakdown' => [
                'wall_area' => $this->round($wallArea),
                'deduction_area' => $this->round($deductionArea),
                'coats' => $coats,
                'coverage_per_liter' => $coverage,
            ],
            'units' => [
                'paint_required' => 'liters',
                'paintable_area' => 'm²',
                'estimated_cost' => 'currency',
            ],
        ];
    }
}
