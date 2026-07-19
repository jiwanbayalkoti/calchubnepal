<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Calories Burned Calculator
 */
class CaloriesBurnedCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'calories_burned_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('weight_kg', 'Weight', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 70, 'unit' => 'kg']),
            $this->field('met', 'Activity MET', 'number', ['min' => 1, 'max' => 18, 'step' => 0.1, 'default' => 7]),
            $this->field('minutes', 'Duration', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 30, 'unit' => 'min']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $kcal = $this->requireNumeric($inputs, 'met') * $this->requireNumeric($inputs, 'weight_kg') * ($this->requireNumeric($inputs, 'minutes') / 60);
        return [
            'results' => ['calories_burned' => $this->round($kcal)],
            'breakdown' => ['formula' => 'kcal = MET × kg × hours'],
            'units' => ['calories_burned' => 'kcal'],
        ];
    }
}
