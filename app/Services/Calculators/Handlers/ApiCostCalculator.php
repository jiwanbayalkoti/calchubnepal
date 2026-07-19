<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * API Cost Calculator
 */
class ApiCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'api_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('requests', 'Monthly Requests', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 100000]),
            $this->field('price_per_1k', 'Price Per 1,000 Requests', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.5, 'unit' => 'USD']),
            $this->field('free_tier', 'Free Tier Requests', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 10000, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $requests = $this->requireNumeric($inputs, 'requests');
        $free = $this->toFloat($inputs, 'free_tier');
        $billable = max(0, $requests - $free);
        $cost = ($billable / 1000) * $this->requireNumeric($inputs, 'price_per_1k');
        return [
            'results' => [
                'billable_requests' => (int) $billable,
                'estimated_monthly_cost' => $this->round($cost, 4),
            ],
            'breakdown' => ['free_tier' => $free],
            'units' => ['billable_requests' => 'requests', 'estimated_monthly_cost' => 'USD'],
        ];
    }
}
