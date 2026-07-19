<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * RPM Calculator
 */
class RpmCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rpm_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('speed', 'Linear Speed', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'm/s']),
            $this->field('diameter', 'Wheel/Pulley Diameter', 'number', ['min' => 0.001, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.5, 'unit' => 'm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $circumference = pi() * $this->requireNumeric($inputs, 'diameter');
        $rps = $this->safeDivide($this->requireNumeric($inputs, 'speed'), $circumference);
        return [
            'results' => ['rpm' => $this->round($rps * 60, 2), 'rps' => $this->round($rps, 4)],
            'breakdown' => ['circumference_m' => $this->round($circumference, 4)],
            'units' => ['rpm' => 'RPM', 'rps' => 'rev/s'],
        ];
    }
}
