<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Inventory Turnover Calculator
 */
class InventoryCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'inventory_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('cogs', 'Cost of Goods Sold', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 500000, 'unit' => 'currency']),
            $this->field('avg_inventory', 'Average Inventory', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $turnover = $this->safeDivide($this->requireNumeric($inputs, 'cogs'), $this->requireNumeric($inputs, 'avg_inventory'));
        $days = $this->safeDivide(365, $turnover);
        return [
            'results' => [
                'turnover_ratio' => $this->round($turnover, 2),
                'days_inventory_outstanding' => $this->round($days, 1),
            ],
            'breakdown' => ['formula' => 'COGS / Avg Inventory'],
            'units' => ['turnover_ratio' => '×', 'days_inventory_outstanding' => 'days'],
        ];
    }
}
