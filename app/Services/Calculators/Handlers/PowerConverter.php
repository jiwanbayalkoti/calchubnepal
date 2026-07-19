<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Power Converter
 */
class PowerConverter extends AbstractCalculatorHandler
{
    protected const FACTORS = array (
  'w' => 1,
  'kw' => 1000,
  'hp' => 745.7,
  'btu_h' => 0.293071,
);

    public function key(): string
    {
        return 'power_converter';
    }

    public function inputSchema(): array
    {
        $units = array_keys(self::FACTORS);

        return [
            $this->field('value', 'Value', 'number', ['min' => -1000000000000, 'max' => 1000000000000, 'step' => 0.000001, 'default' => 1]),
            $this->field('from_unit', 'From Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'kw']),
            $this->field('to_unit', 'To Unit', 'select', ['options' => array_combine($units, $units), 'default' => 'hp']),
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
