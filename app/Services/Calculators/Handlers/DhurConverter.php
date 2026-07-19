<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Dhur Converter
 */
class DhurConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'dhur_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Value', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('from_unit', 'From', 'select', ['options' => ['dhur' => 'Dhur', 'sqft' => 'Square Feet', 'sqm' => 'Square Meter'], 'default' => 'dhur']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // Terai: 1 Dhur ≈ 182.25 sq.ft (common)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'dhur');
        $sqft = match ($from) {
            'sqm' => $value / 0.092903,
            'sqft' => $value,
            default => $value * 182.25,
        };
        return [
            'results' => [
                'dhur' => $this->round($sqft / 182.25, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 3),
            ],
            'breakdown' => ['convention' => '1 Dhur ≈ 182.25 sq.ft (Terai)'],
            'units' => ['dhur' => 'dhur', 'square_feet' => 'sq.ft', 'square_meters' => 'm²'],
        ];
    }
}
