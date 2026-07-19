<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Torque Calculator
 */
class TorqueCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'torque_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('force', 'Force', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100, 'unit' => 'N']),
            $this->field('lever_arm', 'Lever Arm', 'number', ['min' => 0.001, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.5, 'unit' => 'm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $torque = $this->requireNumeric($inputs, 'force') * $this->requireNumeric($inputs, 'lever_arm');
        return [
            'results' => ['torque' => $this->round($torque, 3)],
            'breakdown' => ['formula' => 'τ = F × r'],
            'units' => ['torque' => 'N·m'],
        ];
    }
}
