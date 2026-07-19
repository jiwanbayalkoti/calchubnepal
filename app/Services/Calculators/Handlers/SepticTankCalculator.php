<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Septic Tank Calculator
 */
class SepticTankCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'septic_tank_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('users', 'Number of Users', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 5]),
            $this->field('liters_per_person', 'Liters Per Person / Day', 'number', ['min' => 50, 'max' => 300, 'step' => 0.01, 'default' => 100]),
            $this->field('retention_days', 'Retention Days', 'number', ['min' => 1, 'max' => 10, 'step' => 1, 'default' => 3]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $users = $this->requireNumeric($inputs, 'users');
        $lpp = $this->requireNumeric($inputs, 'liters_per_person');
        $days = $this->requireNumeric($inputs, 'retention_days');
        $liters = $users * $lpp * $days;
        return [
            'results' => [
                'tank_capacity_liters' => $this->round($liters),
                'tank_capacity_m3' => $this->round($liters / 1000, 3),
            ],
            'breakdown' => ['daily_flow_liters' => $this->round($users * $lpp)],
            'units' => ['tank_capacity_liters' => 'L', 'tank_capacity_m3' => 'm³'],
        ];
    }
}
