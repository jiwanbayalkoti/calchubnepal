<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts a length value between common units by normalizing to meters
 * (the base unit) and then converting to the requested target unit.
 */
class LengthConverter extends AbstractCalculatorHandler
{
    protected const FACTORS_TO_METERS = [
        'mm' => 0.001,
        'cm' => 0.01,
        'm' => 1,
        'km' => 1000,
        'in' => 0.0254,
        'ft' => 0.3048,
        'yd' => 0.9144,
        'mile' => 1609.344,
    ];

    public function key(): string
    {
        return 'length_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS_TO_METERS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'm']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'ft']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'm');
        $toUnit = $this->toString($inputs, 'to_unit', 'ft');

        if (! isset(self::FACTORS_TO_METERS[$fromUnit]) || ! isset(self::FACTORS_TO_METERS[$toUnit])) {
            throw new InvalidArgumentException('Unsupported length unit provided.');
        }

        $valueInMeters = $value * self::FACTORS_TO_METERS[$fromUnit];
        $convertedValue = $valueInMeters / self::FACTORS_TO_METERS[$toUnit];

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 6),
            ],
            'breakdown' => [
                'value_in_meters' => $this->round($valueInMeters, 6),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }
}
