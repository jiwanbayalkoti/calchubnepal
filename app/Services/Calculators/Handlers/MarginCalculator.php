<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Pricing calculator: given a unit cost and a desired gross margin
 * percentage, derives the selling price required to hit that margin.
 * Margin = (Price - Cost) / Price, distinct from markup = (Price - Cost) / Cost.
 */
class MarginCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'margin_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('cost_price', 'Cost Price', 'number', ['unit' => 'currency', 'min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 50]),
            $this->field('desired_margin_percent', 'Desired Margin', 'number', ['unit' => '%', 'min' => 0, 'max' => 99, 'step' => 0.01, 'default' => 30]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $costPrice = $this->requireNumeric($inputs, 'cost_price');
        $desiredMargin = $this->requireNumeric($inputs, 'desired_margin_percent');

        $marginFraction = min(0.99, max(0, $desiredMargin / 100));
        $sellingPrice = $this->safeDivide($costPrice, 1 - $marginFraction);
        $profit = $sellingPrice - $costPrice;
        $markupPercent = $this->percentageOf($profit, $costPrice);

        return [
            'results' => [
                'selling_price' => $this->round($sellingPrice),
                'profit' => $this->round($profit),
                'markup_percent' => $this->round($markupPercent),
            ],
            'breakdown' => [
                'cost_price' => $this->round($costPrice),
                'margin_percent' => $desiredMargin,
            ],
            'units' => [
                'selling_price' => 'currency',
                'profit' => 'currency',
                'markup_percent' => '%',
            ],
        ];
    }
}
