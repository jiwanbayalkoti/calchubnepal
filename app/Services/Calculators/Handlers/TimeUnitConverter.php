<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Time Unit Converter
 */
class TimeUnitConverter extends AbstractCalculatorHandler
{
    protected const FACTORS = array (
  'sec' => 1,
  'min' => 60,
  'hour' => 3600,
  'day' => 86400,
  'week' => 604800,
);

    public function key(): string
    {
        return 'time_unit_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000000, 'max' => 1000000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'hour']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'min']),
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
