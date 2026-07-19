<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Converts a temperature value between Celsius, Fahrenheit and Kelvin.
 * Temperature conversion is non-linear across scales (offset differs),
 * so each value is first normalized to Celsius and then converted to
 * the requested target scale.
 */
class TemperatureConverter extends AbstractCalculatorHandler
{
    protected const UNITS = ['celsius', 'fahrenheit', 'kelvin'];

    public function key(): string
    {
        return 'temperature_converter';
    }

    public function inputSchema(): array
    {
        $options = array_combine(self::UNITS, ['Celsius (°C)', 'Fahrenheit (°F)', 'Kelvin (K)']);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000, 'max' => 1000000, 'step' => 0.01, 'default' => 25]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => $options, 'default' => 'celsius']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => $options, 'default' => 'fahrenheit']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'celsius');
        $toUnit = $this->toString($inputs, 'to_unit', 'fahrenheit');

        if (! in_array($fromUnit, self::UNITS, true) || ! in_array($toUnit, self::UNITS, true)) {
            throw new InvalidArgumentException('Unsupported temperature unit provided.');
        }

        $celsius = $this->toCelsius($value, $fromUnit);
        $convertedValue = $this->fromCelsius($celsius, $toUnit);

        return [
            'results' => [
                'converted_value' => $this->round($convertedValue, 2),
            ],
            'breakdown' => [
                'value_in_celsius' => $this->round($celsius, 2),
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }

    protected function toCelsius(float $value, string $unit): float
    {
        return match ($unit) {
            'fahrenheit' => ($value - 32) * 5 / 9,
            'kelvin' => $value - 273.15,
            default => $value,
        };
    }

    protected function fromCelsius(float $celsius, string $unit): float
    {
        return match ($unit) {
            'fahrenheit' => ($celsius * 9 / 5) + 32,
            'kelvin' => $celsius + 273.15,
            default => $celsius,
        };
    }
}
