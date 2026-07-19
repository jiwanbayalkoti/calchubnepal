<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Calculates the difference between two dates in years/months/days as
 * well as total days, weeks and business days (Mon-Fri) between them.
 */
class DateDifferenceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'date_difference_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('start_date', 'Start Date', 'date', []),
            $this->field('end_date', 'End Date', 'date', []),
        ];
    }

    public function calculate(array $inputs): array
    {
        $startRaw = $this->toString($inputs, 'start_date');
        $endRaw = $this->toString($inputs, 'end_date');

        if ($startRaw === '' || $endRaw === '') {
            throw new InvalidArgumentException('Both [start_date] and [end_date] are required.');
        }

        $start = Carbon::parse($startRaw)->startOfDay();
        $end = Carbon::parse($endRaw)->startOfDay();

        $swapped = false;
        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
            $swapped = true;
        }

        $diff = $start->diff($end);
        $totalDays = $start->diffInDays($end);
        $totalWeeks = intdiv($totalDays, 7);

        $businessDays = 0;
        $cursor = $start->copy();
        while ($cursor->lessThan($end)) {
            if (! $cursor->isWeekend()) {
                $businessDays++;
            }
            $cursor->addDay();
        }

        return [
            'results' => [
                'years' => $diff->y,
                'months' => $diff->m,
                'days' => $diff->d,
                'total_days' => $totalDays,
            ],
            'breakdown' => [
                'total_weeks' => $totalWeeks,
                'business_days' => $businessDays,
                'dates_were_swapped' => $swapped,
            ],
            'units' => [
                'years' => 'years',
                'months' => 'months',
                'days' => 'days',
                'total_days' => 'days',
            ],
        ];
    }
}
