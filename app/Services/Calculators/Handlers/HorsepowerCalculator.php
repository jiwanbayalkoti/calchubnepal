<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Horsepower Calculator
 */
class HorsepowerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'horsepower_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('torque_nm', 'Torque', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 200, 'unit' => 'N·m']),
            $this->field('rpm', 'RPM', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 3000]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $hp = ($this->requireNumeric($inputs, 'torque_nm') * $this->requireNumeric($inputs, 'rpm')) / 7127;
        $kw = $hp * 0.7457;
        return [
            'results' => ['horsepower' => $this->round($hp, 3), 'kilowatts' => $this->round($kw, 3)],
            'breakdown' => ['formula' => 'HP = τ × RPM / 7127'],
            'units' => ['horsepower' => 'HP', 'kilowatts' => 'kW'],
        ];
    }
}
