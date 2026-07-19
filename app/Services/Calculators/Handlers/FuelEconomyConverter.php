<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Fuel Economy Converter
 */
class FuelEconomyConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'fuel_economy_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Value', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 15]),
            $this->field('from_unit', 'From', 'select', ['options' => ['km_l' => 'km/L', 'l_100km' => 'L/100km', 'mpg_us' => 'MPG (US)', 'mpg_uk' => 'MPG (UK)'], 'default' => 'km_l']),
            $this->field('to_unit', 'To', 'select', ['options' => ['km_l' => 'km/L', 'l_100km' => 'L/100km', 'mpg_us' => 'MPG (US)', 'mpg_uk' => 'MPG (UK)'], 'default' => 'mpg_us']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'km_l');
        $to = $this->toString($inputs, 'to_unit', 'mpg_us');
        $toKmL = match ($from) {
            'l_100km' => $this->safeDivide(100, $value),
            'mpg_us' => $value * 0.425144,
            'mpg_uk' => $value * 0.354006,
            default => $value,
        };
        $converted = match ($to) {
            'l_100km' => $this->safeDivide(100, $toKmL),
            'mpg_us' => $toKmL / 0.425144,
            'mpg_uk' => $toKmL / 0.354006,
            default => $toKmL,
        };
        return [
            'results' => ['converted_value' => $this->round($converted, 4)],
            'breakdown' => ['as_km_per_l' => $this->round($toKmL, 4)],
            'units' => ['converted_value' => $to],
        ];
    }
}
