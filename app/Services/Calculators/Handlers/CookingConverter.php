<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Cooking Converter
 */
class CookingConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'cooking_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('from_unit', 'From', 'select', ['options' => ['tsp' => 'Teaspoon', 'tbsp' => 'Tablespoon', 'cup' => 'Cup', 'ml' => 'Milliliter', 'g' => 'Gram (water)'], 'default' => 'cup']),
            $this->field('to_unit', 'To', 'select', ['options' => ['tsp' => 'Teaspoon', 'tbsp' => 'Tablespoon', 'cup' => 'Cup', 'ml' => 'Milliliter', 'g' => 'Gram (water)'], 'default' => 'ml']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $toMl = ['tsp' => 4.92892, 'tbsp' => 14.7868, 'cup' => 240, 'ml' => 1, 'g' => 1];
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'cup');
        $to = $this->toString($inputs, 'to_unit', 'ml');
        $ml = $value * $toMl[$from];
        $converted = $ml / $toMl[$to];
        return [
            'results' => ['converted_value' => $this->round($converted, 4)],
            'breakdown' => ['ml_equivalent' => $this->round($ml, 4)],
            'units' => ['converted_value' => $to],
        ];
    }
}
