<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Recipe Scaler
 */
class RecipeScalerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'recipe_scaler_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('original_servings', 'Original Servings', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 4]),
            $this->field('desired_servings', 'Desired Servings', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 6]),
            $this->field('ingredient_amount', 'Ingredient Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $factor = $this->safeDivide($this->requireNumeric($inputs, 'desired_servings'), $this->requireNumeric($inputs, 'original_servings'));
        $scaled = $this->requireNumeric($inputs, 'ingredient_amount') * $factor;
        return [
            'results' => ['scale_factor' => $this->round($factor, 4), 'scaled_amount' => $this->round($scaled, 3)],
            'breakdown' => ['formula' => 'desired / original'],
            'units' => ['scale_factor' => '×', 'scaled_amount' => 'amount'],
        ];
    }
}
