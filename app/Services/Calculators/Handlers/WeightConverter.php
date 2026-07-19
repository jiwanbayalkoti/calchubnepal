<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts a weight/mass value between common units by normalizing to
 * kilograms (the base unit) and then converting to the requested target
 * unit.
 */
class WeightConverter extends AbstractCalculatorHandler
{
    protected const FACTORS_TO_KG = [
        'mg' => 0.000001,
        'g' => 0.001,
        'kg' => 1,
        'ton_metric' => 1000,
        'lb' => 0.45359237,
        'oz' => 0.028349523125,
        'stone' => 6.35029318,
    ];

    public function key(): string
    {
        return 'weight_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS_TO_KG);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'kg']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'lb']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'kg');
        $toUnit = $this->toString($inputs, 'to_unit', 'lb');

        if (! isset(self::FACTORS_TO_KG[$fromUnit]) || ! isset(self::FACTORS_TO_KG[$toUnit])) {
            throw new InvalidArgumentException('Unsupported weight unit provided.');
        }

        $valueInKg = $value * self::FACTORS_TO_KG[$fromUnit];
        $convertedValue = $valueInKg / self::FACTORS_TO_KG[$toUnit];

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 6),
            ],
            'breakdown' => [
                'value_in_kg' => $this->round($valueInKg, 6),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }
}
