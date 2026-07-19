<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Speed / Time / Distance
 */
class VehicleSpeedCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'vehicle_speed_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('solve_for', 'Solve For', 'select', ['options' => ['speed' => 'Speed', 'time' => 'Time', 'distance' => 'Distance'], 'default' => 'speed']),
            $this->field('distance', 'Distance (km)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 120, 'required' => false]),
            $this->field('time_hours', 'Time (hours)', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'required' => false]),
            $this->field('speed', 'Speed (km/h)', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 60, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $mode = $this->toString($inputs, 'solve_for', 'speed');
        $d = $this->toFloat($inputs, 'distance');
        $t = $this->toFloat($inputs, 'time_hours');
        $s = $this->toFloat($inputs, 'speed');
        $value = match ($mode) {
            'time' => $this->safeDivide($d, $s),
            'distance' => $s * $t,
            default => $this->safeDivide($d, $t),
        };
        $label = match ($mode) { 'time' => 'time_hours', 'distance' => 'distance_km', default => 'speed_kmh' };
        return [
            'results' => [$label => $this->round($value, 3)],
            'breakdown' => ['formula' => 'distance = speed × time'],
            'units' => [$label => match ($mode) { 'time' => 'hours', 'distance' => 'km', default => 'km/h' }],
        ];
    }
}
