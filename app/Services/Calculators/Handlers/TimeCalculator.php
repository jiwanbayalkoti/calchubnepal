<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Time duration calculator supporting two modes:
 * - difference: elapsed time between a start and end time (HH:mm),
 *   automatically rolling over midnight when end < start.
 * - add: adds a given number of hours/minutes to a start time.
 */
class TimeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'time_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('mode', 'Mode', 'select', [
                'options' => ['difference' => 'Time Difference', 'add' => 'Add/Subtract Time'],
                'default' => 'difference',
            ]),
            $this->field('start_time', 'Start Time (HH:MM)', 'time', []),
            $this->field('end_time', 'End Time (HH:MM)', 'time', ['required' => false]),
            $this->field('hours_to_add', 'Hours to Add', 'number', ['min' => -1000, 'max' => 1000, 'step' => 1, 'default' => 0, 'required' => false]),
            $this->field('minutes_to_add', 'Minutes to Add', 'number', ['min' => -1000, 'max' => 1000, 'step' => 1, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $mode = $this->toString($inputs, 'mode', 'difference');
        $startRaw = $this->toString($inputs, 'start_time');

        if ($startRaw === '') {
            throw new InvalidArgumentException('The field [start_time] is required.');
        }

        $start = Carbon::createFromFormat('H:i', $startRaw);

        if ($mode === 'add') {
            $hours = $this->toFloat($inputs, 'hours_to_add', 0);
            $minutes = $this->toFloat($inputs, 'minutes_to_add', 0);

            $result = $start->copy()->addMinutes((int) round($hours * 60 + $minutes));

            return [
                'results' => [
                    'result_time' => $result->format('H:i'),
                ],
                'breakdown' => [
                    'start_time' => $start->format('H:i'),
                    'minutes_applied' => (int) round($hours * 60 + $minutes),
                ],
                'units' => [
                    'result_time' => 'HH:MM',
                ],
            ];
        }

        $endRaw = $this->toString($inputs, 'end_time');

        if ($endRaw === '') {
            throw new InvalidArgumentException('The field [end_time] is required for difference mode.');
        }

        $end = Carbon::createFromFormat('H:i', $endRaw);
        $rolledOver = false;

        if ($end->lessThan($start)) {
            $end->addDay();
            $rolledOver = true;
        }

        $totalMinutes = $start->diffInMinutes($end);

        return [
            'results' => [
                'hours' => intdiv($totalMinutes, 60),
                'minutes' => $totalMinutes % 60,
                'total_minutes' => $totalMinutes,
            ],
            'breakdown' => [
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'rolled_over_midnight' => $rolledOver,
            ],
            'units' => [
                'hours' => 'hours',
                'minutes' => 'minutes',
                'total_minutes' => 'minutes',
            ],
        ];
    }
}
