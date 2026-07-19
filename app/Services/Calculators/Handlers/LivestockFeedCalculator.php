<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Livestock Feed Calculator
 */
class LivestockFeedCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'livestock_feed_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('animals', 'Number of Animals', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 10]),
            $this->field('feed_per_day', 'Feed Per Animal / Day', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2.5, 'unit' => 'kg']),
            $this->field('days', 'Days', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 30]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $total = $this->requireNumeric($inputs, 'animals') * $this->requireNumeric($inputs, 'feed_per_day') * $this->requireNumeric($inputs, 'days');
        return [
            'results' => ['total_feed_kg' => $this->round($total, 1), 'total_feed_tons' => $this->round($total / 1000, 3)],
            'breakdown' => [],
            'units' => ['total_feed_kg' => 'kg', 'total_feed_tons' => 'tons'],
        ];
    }
}
