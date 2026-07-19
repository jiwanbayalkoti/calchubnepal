<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Decimal Calculator
 */
class DecimalCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'decimal_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Decimal Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.0001, 'default' => 0.75]),
            $this->field('places', 'Round To Places', 'number', ['min' => 0, 'max' => 10, 'step' => 1, 'default' => 2]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $places = (int) $this->requireNumeric($inputs, 'places');
        $rounded = round($value, $places);
        $percent = $value * 100;

        return [
            'results' => [
                'rounded' => $this->round($rounded, $places),
                'as_percent' => $this->round($percent, 4),
                'as_fraction_approx' => $this->round($value, 6),
            ],
            'breakdown' => ['original' => $value, 'places' => $places],
            'units' => ['rounded' => 'number', 'as_percent' => '%', 'as_fraction_approx' => 'decimal'],
        ];
    }
}
