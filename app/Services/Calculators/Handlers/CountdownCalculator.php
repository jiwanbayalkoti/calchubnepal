<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Countdown Calculator
 */
class CountdownCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'countdown_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('target_date', 'Target Date', 'date', ['default' => '2026-08-17']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $target = \Carbon\Carbon::parse($this->toString($inputs, 'target_date'))->startOfDay();
        $now = now()->startOfDay();
        $days = $now->diffInDays($target, false);
        return [
            'results' => [
                'days_remaining' => $days,
                'weeks_remaining' => $this->round($days / 7, 1),
                'status' => $days > 0 ? 'Upcoming' : ($days == 0 ? 'Today' : 'Passed'),
            ],
            'breakdown' => ['target_date' => $target->toDateString()],
            'units' => ['days_remaining' => 'days', 'weeks_remaining' => 'weeks', 'status' => 'text'],
        ];
    }
}
