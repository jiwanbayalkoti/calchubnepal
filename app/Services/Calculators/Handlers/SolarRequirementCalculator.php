<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Home Solar Requirement
 */
class SolarRequirementCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'solar_requirement_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_kwh', 'Monthly Usage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 300, 'unit' => 'kWh']),
            $this->field('sun_hours', 'Peak Sun Hours', 'number', ['min' => 1, 'max' => 8, 'step' => 0.01, 'default' => 4.5]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $daily = $this->requireNumeric($inputs, 'monthly_kwh') / 30;
        $kw = $this->safeDivide($daily, $this->requireNumeric($inputs, 'sun_hours') * 0.8);
        return [
            'results' => [
                'daily_kwh' => $this->round($daily, 2),
                'recommended_system_kw' => $this->round($kw, 2),
            ],
            'breakdown' => ['efficiency_assumed' => '80%'],
            'units' => ['daily_kwh' => 'kWh/day', 'recommended_system_kw' => 'kW'],
        ];
    }
}
