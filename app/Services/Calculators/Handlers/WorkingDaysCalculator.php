<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Working Days Calculator
 */
class WorkingDaysCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'working_days_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('start_date', 'Start Date', 'date', ['default' => '2026-07-18']),
            $this->field('end_date', 'End Date', 'date', ['default' => '2026-08-01']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $start = \Carbon\Carbon::parse($this->toString($inputs, 'start_date'))->startOfDay();
        $end = \Carbon\Carbon::parse($this->toString($inputs, 'end_date'))->startOfDay();
        if ($end->lt($start)) {
            throw new InvalidArgumentException('End date must be on or after start date.');
        }
        $working = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isWeekend()) {
                $working++;
            }
        }
        return [
            'results' => [
                'working_days' => $working,
                'calendar_days' => $start->diffInDays($end) + 1,
                'weekend_days' => ($start->diffInDays($end) + 1) - $working,
            ],
            'breakdown' => ['excludes' => 'Saturday & Sunday'],
            'units' => ['working_days' => 'days', 'calendar_days' => 'days', 'weekend_days' => 'days'],
        ];
    }
}
