<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pipe Flow Calculator
 */
class PipeFlowCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pipe_flow_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('diameter', 'Diameter', 'number', ['min' => 0.001, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.05, 'unit' => 'm']),
            $this->field('velocity', 'Flow Velocity', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 1.5, 'unit' => 'm/s']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = pi() * (($this->requireNumeric($inputs, 'diameter') / 2) ** 2);
        $q = $area * $this->requireNumeric($inputs, 'velocity');
        return [
            'results' => [
                'flow_m3_per_s' => $this->round($q, 6),
                'flow_liters_per_s' => $this->round($q * 1000, 3),
                'flow_m3_per_hour' => $this->round($q * 3600, 3),
            ],
            'breakdown' => ['cross_section_m2' => $this->round($area, 6)],
            'units' => ['flow_m3_per_s' => 'm³/s', 'flow_liters_per_s' => 'L/s', 'flow_m3_per_hour' => 'm³/h'],
        ];
    }
}
