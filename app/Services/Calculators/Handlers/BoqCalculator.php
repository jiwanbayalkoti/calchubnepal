<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * BOQ Estimator
 */
class BoqCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'boq_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('quantity', 'Quantity', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100]),
            $this->field('unit_rate', 'Unit Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50, 'unit' => 'currency']),
            $this->field('wastage', 'Wastage / Contingency', 'number', ['min' => 0, 'max' => 25, 'step' => 0.01, 'default' => 5, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $qty = $this->requireNumeric($inputs, 'quantity');
        $rate = $this->requireNumeric($inputs, 'unit_rate');
        $waste = $this->requireNumeric($inputs, 'wastage');
        $adjustedQty = $qty * (1 + $waste / 100);
        $amount = $adjustedQty * $rate;
        return [
            'results' => [
                'adjusted_quantity' => $this->round($adjustedQty, 2),
                'line_amount' => $this->round($amount),
            ],
            'breakdown' => ['base_quantity' => $qty, 'unit_rate' => $rate],
            'units' => ['adjusted_quantity' => 'qty', 'line_amount' => 'currency'],
        ];
    }
}
