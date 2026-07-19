<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts an area value between common units by normalizing to square
 * meters (the base unit) and then converting to the requested target unit.
 */
class AreaConverter extends AbstractCalculatorHandler
{
    protected const FACTORS_TO_SQM = [
        'sq_mm' => 0.000001,
        'sq_cm' => 0.0001,
        'sq_m' => 1,
        'hectare' => 10000,
        'sq_km' => 1000000,
        'sq_ft' => 0.09290304,
        'sq_yd' => 0.83612736,
        'acre' => 4046.8564224,
        'sq_mile' => 2589988.110336,
    ];

    public function key(): string
    {
        return 'area_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS_TO_SQM);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'sq_m']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'sq_ft']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'sq_m');
        $toUnit = $this->toString($inputs, 'to_unit', 'sq_ft');

        if (! isset(self::FACTORS_TO_SQM[$fromUnit]) || ! isset(self::FACTORS_TO_SQM[$toUnit])) {
            throw new InvalidArgumentException('Unsupported area unit provided.');
        }

        $valueInSqm = $value * self::FACTORS_TO_SQM[$fromUnit];
        $convertedValue = $valueInSqm / self::FACTORS_TO_SQM[$toUnit];

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 6),
            ],
            'breakdown' => [
                'value_in_sq_meters' => $this->round($valueInSqm, 6),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }
}
