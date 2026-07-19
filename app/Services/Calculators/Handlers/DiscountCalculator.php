<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Successive discount calculator: applies up to two sequential discount
 * percentages to an original price and reports total savings and the
 * single effective discount percentage that produces the same result.
 */
class DiscountCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'discount_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('original_price', 'Original Price', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100]),
            $this->field('discount_percent', 'Discount', 'number', ['unit' => '%', 'min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 20]),
            $this->field('additional_discount_percent', 'Additional Discount', 'number', ['unit' => '%', 'min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $originalPrice = $this->requireNumeric($inputs, 'original_price');
        $discount1 = $this->requireNumeric($inputs, 'discount_percent');
        $discount2 = $this->toFloat($inputs, 'additional_discount_percent', 0);

        $priceAfterFirst = $originalPrice * (1 - $discount1 / 100);
        $finalPrice = $priceAfterFirst * (1 - $discount2 / 100);

        $totalSavings = $originalPrice - $finalPrice;
        $effectiveDiscountPercent = $this->percentageOf($totalSavings, $originalPrice);

        return [
            'results' => [
                'final_price' => $this->round($finalPrice),
                'total_savings' => $this->round($totalSavings),
                'effective_discount_percent' => $this->round($effectiveDiscountPercent),
            ],
            'breakdown' => [
                'price_after_first_discount' => $this->round($priceAfterFirst),
                'original_price' => $this->round($originalPrice),
            ],
            'units' => [
                'final_price' => 'currency',
                'total_savings' => 'currency',
                'effective_discount_percent' => '%',
            ],
        ];
    }
}
