<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Energy Converter
 */
class EnergyConverter extends AbstractCalculatorHandler
{
    protected const FACTORS = array (
  'j' => 1,
  'kj' => 1000,
  'cal' => 4.184,
  'kcal' => 4184,
  'wh' => 3600,
  'kwh' => 3600000,
  'btu' => 1055.06,
);

    public function key(): string
    {
        return 'energy_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000000, 'max' => 1000000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'kwh']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'kj']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit');
        $to = $this->toString($inputs, 'to_unit');

        if (! isset(self::FACTORS[$from], self::FACTORS[$to])) {
            throw new InvalidArgumentException('Unsupported unit.');
        }

        $base = $value * self::FACTORS[$from];
        $converted = $base / self::FACTORS[$to];

        return [
            'results' => ['converted_value' => $this->round($converted, 8)],
            'breakdown' => ['base_value' => $this->round($base, 8), 'from_unit' => $from, 'to_unit' => $to],
            'units' => ['converted_value' => $to],
        ];
    }
}
