<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts a volume value between common units by normalizing to liters
 * (the base unit) and then converting to the requested target unit.
 */
class VolumeConverter extends AbstractCalculatorHandler
{
    protected const FACTORS_TO_LITERS = [
        'ml' => 0.001,
        'l' => 1,
        'm3' => 1000,
        'cm3' => 0.001,
        'gallon_us' => 3.785411784,
        'gallon_uk' => 4.54609,
        'quart_us' => 0.946352946,
        'cubic_ft' => 28.3168466,
        'cubic_in' => 0.016387064,
    ];

    public function key(): string
    {
        return 'volume_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS_TO_LITERS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'l']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'gallon_us']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'l');
        $toUnit = $this->toString($inputs, 'to_unit', 'gallon_us');

        if (! isset(self::FACTORS_TO_LITERS[$fromUnit]) || ! isset(self::FACTORS_TO_LITERS[$toUnit])) {
            throw new InvalidArgumentException('Unsupported volume unit provided.');
        }

        $valueInLiters = $value * self::FACTORS_TO_LITERS[$fromUnit];
        $convertedValue = $valueInLiters / self::FACTORS_TO_LITERS[$toUnit];

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 6),
            ],
            'breakdown' => [
                'value_in_liters' => $this->round($valueInLiters, 6),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }
}
