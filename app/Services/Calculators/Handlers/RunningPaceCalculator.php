<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Running Pace Calculator
 */
class RunningPaceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'running_pace_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('distance_km', 'Distance', 'number', ['min' => 0.1, 'max' => 1000000000, 'step' => 0.01, 'default' => 5, 'unit' => 'km']),
            $this->field('hours', 'Hours', 'number', ['min' => 0, 'max' => 24, 'step' => 1, 'default' => 0]),
            $this->field('minutes', 'Minutes', 'number', ['min' => 0, 'max' => 59, 'step' => 1, 'default' => 28]),
            $this->field('seconds', 'Seconds', 'number', ['min' => 0, 'max' => 59, 'step' => 1, 'default' => 0]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $dist = $this->requireNumeric($inputs, 'distance_km');
        $totalMin = $this->requireNumeric($inputs, 'hours') * 60 + $this->requireNumeric($inputs, 'minutes') + $this->requireNumeric($inputs, 'seconds') / 60;
        $pace = $this->safeDivide($totalMin, $dist);
        $paceMin = (int) floor($pace);
        $paceSec = (int) round(($pace - $paceMin) * 60);
        $speed = $this->safeDivide($dist, $totalMin / 60);
        return [
            'results' => [
                'pace_per_km' => sprintf('%d:%02d', $paceMin, $paceSec),
                'speed_kmh' => $this->round($speed, 2),
            ],
            'breakdown' => ['total_minutes' => $this->round($totalMin, 2)],
            'units' => ['pace_per_km' => 'min/km', 'speed_kmh' => 'km/h'],
        ];
    }
}
