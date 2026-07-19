<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Sleep Cycle Calculator
 */
class SleepCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'sleep_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('wake_time', 'Wake-up Time', 'time', ['default' => '06:30']),
            $this->field('cycles', 'Sleep Cycles (90 min)', 'number', ['min' => 1, 'max' => 8, 'step' => 1, 'default' => 5]),
        ];
    }

    public function calculate(array $inputs): array
    {
        [$h, $m] = array_map('intval', explode(':', $this->toString($inputs, 'wake_time', '06:30')));
        $wake = $h * 60 + $m;
        $cycles = (int) $this->requireNumeric($inputs, 'cycles');
        $bed = $wake - ($cycles * 90) - 15; // 15 min fall-asleep buffer
        while ($bed < 0) { $bed += 1440; }
        $bedTime = sprintf('%02d:%02d', intdiv($bed, 60), $bed % 60);
        return [
            'results' => [
                'suggested_bedtime' => $bedTime,
                'sleep_duration_hours' => $this->round(($cycles * 90) / 60, 1),
            ],
            'breakdown' => ['cycles' => $cycles, 'cycle_minutes' => 90],
            'units' => ['suggested_bedtime' => 'HH:MM', 'sleep_duration_hours' => 'hours'],
        ];
    }
}
