<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * UPS Size Calculator
 */
class UpsCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ups_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('load_watts', 'Total Load', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 800, 'unit' => 'W']),
            $this->field('power_factor', 'Power Factor', 'number', ['min' => 0.5, 'max' => 1, 'step' => 0.01, 'default' => 0.8]),
            $this->field('headroom_percent', 'Headroom', 'number', ['min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 25, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $watts = $this->requireNumeric($inputs, 'load_watts');
        $pf = $this->requireNumeric($inputs, 'power_factor');
        $head = 1 + $this->requireNumeric($inputs, 'headroom_percent') / 100;
        $va = $this->safeDivide($watts, $pf) * $head;
        return [
            'results' => ['recommended_ups_va' => $this->round($va), 'recommended_ups_kva' => $this->round($va / 1000, 2)],
            'breakdown' => ['load_watts' => $watts],
            'units' => ['recommended_ups_va' => 'VA', 'recommended_ups_kva' => 'kVA'],
        ];
    }
}
