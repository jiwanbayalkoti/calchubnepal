<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Computes total profit, profit margin and markup for a batch of goods
 * given unit cost and unit selling price.
 */
class ProfitCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'profit_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('cost_price', 'Unit Cost Price', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50]),
            $this->field('selling_price', 'Unit Selling Price', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 75]),
            $this->field('quantity', 'Quantity', 'number', ['min' => 1, 'max' => 10000000, 'step' => 1, 'default' => 1, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $costPrice = $this->requireNumeric($inputs, 'cost_price');
        $sellingPrice = $this->requireNumeric($inputs, 'selling_price');
        $quantity = $this->toFloat($inputs, 'quantity', 1);

        $totalCost = $costPrice * $quantity;
        $totalRevenue = $sellingPrice * $quantity;
        $profit = $totalRevenue - $totalCost;

        $marginPercent = $this->percentageOf($profit, $totalRevenue);
        $markupPercent = $this->percentageOf($profit, $totalCost);

        return [
            'results' => [
                'total_profit' => $this->round($profit),
                'profit_margin_percent' => $this->round($marginPercent),
                'markup_percent' => $this->round($markupPercent),
            ],
            'breakdown' => [
                'total_cost' => $this->round($totalCost),
                'total_revenue' => $this->round($totalRevenue),
                'profit_per_unit' => $this->round($sellingPrice - $costPrice),
            ],
            'units' => [
                'total_profit' => 'currency',
                'profit_margin_percent' => '%',
                'markup_percent' => '%',
            ],
        ];
    }
}
