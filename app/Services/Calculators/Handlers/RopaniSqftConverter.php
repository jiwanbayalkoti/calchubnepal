<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Ropani ↔ Square Feet
 */
class RopaniSqftConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ropani_sqft_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Value', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('from_unit', 'From', 'select', ['options' => ['ropani' => 'Ropani', 'sqft' => 'Square Feet'], 'default' => 'ropani']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // 1 Ropani = 5476 sq.ft (common Nepal convention)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'ropani');
        $sqft = $from === 'ropani' ? $value * 5476 : $value;
        $ropani = $sqft / 5476;
        return [
            'results' => [
                'ropani' => $this->round($ropani, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 2),
            ],
            'breakdown' => ['convention' => '1 Ropani = 5476 sq.ft'],
            'units' => ['ropani' => 'ropani', 'square_feet' => 'sq.ft', 'square_meters' => 'm²'],
        ];
    }
}
