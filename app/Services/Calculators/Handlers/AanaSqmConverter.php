<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Aana ↔ Square Meter
 */
class AanaSqmConverter extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'aana_sqm_converter';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('value', 'Value', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('from_unit', 'From', 'select', ['options' => ['aana' => 'Aana', 'sqm' => 'Square Meter'], 'default' => 'aana']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // 1 Aana = 31.796 m² (approx; 1 Ropani = 16 Aana = 508.72 m²)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'aana');
        $sqm = $from === 'aana' ? $value * 31.796 : $value;
        $aana = $sqm / 31.796;
        return [
            'results' => [
                'aana' => $this->round($aana, 6),
                'square_meters' => $this->round($sqm, 3),
                'square_feet' => $this->round($sqm / 0.092903, 2),
            ],
            'breakdown' => ['convention' => '1 Aana ≈ 31.796 m²'],
            'units' => ['aana' => 'aana', 'square_meters' => 'm²', 'square_feet' => 'sq.ft'],
        ];
    }
}
