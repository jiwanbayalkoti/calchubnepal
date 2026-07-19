<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Cycling Speed Calculator
 */
class CyclingSpeedCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'cycling_speed_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance_km', 'Distance', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 40, 'unit' => 'km']),
            $this->field('hours', 'Hours', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1]),
            $this->field('minutes', 'Minutes', 'number', ['min' => 0, 'max' => 59, 'step' => 0.01, 'default' => 30]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $hours = $this->requireNumeric($inputs, 'hours') + $this->requireNumeric($inputs, 'minutes') / 60;
        $speed = $this->safeDivide($this->requireNumeric($inputs, 'distance_km'), $hours);
        return [
            'results' => ['average_speed_kmh' => $this->round($speed, 2)],
            'breakdown' => ['time_hours' => $this->round($hours, 3)],
            'units' => ['average_speed_kmh' => 'km/h'],
        ];
    }
}
