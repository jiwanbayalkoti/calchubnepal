<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts a speed value between common units by normalizing to meters
 * per second (the base unit) and then converting to the requested
 * target unit.
 */
class SpeedConverter extends AbstractCalculatorHandler
{
    protected const FACTORS_TO_MPS = [
        'mps' => 1,
        'kmph' => 0.2777777778,
        'mph' => 0.44704,
        'knot' => 0.5144444444,
        'fps' => 0.3048,
    ];

    public function key(): string
    {
        return 'speed_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS_TO_MPS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'kmph']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'mph']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'kmph');
        $toUnit = $this->toString($inputs, 'to_unit', 'mph');

        if (! isset(self::FACTORS_TO_MPS[$fromUnit]) || ! isset(self::FACTORS_TO_MPS[$toUnit])) {
            throw new InvalidArgumentException('Unsupported speed unit provided.');
        }

        $valueInMps = $value * self::FACTORS_TO_MPS[$fromUnit];
        $convertedValue = $valueInMps / self::FACTORS_TO_MPS[$toUnit];

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 6),
            ],
            'breakdown' => [
                'value_in_mps' => $this->round($valueInMps, 6),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }
}
