<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Solar Panel Calculator
 */
class SolarPanelCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'solar_panel_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('daily_kwh', 'Daily Energy Need', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'kWh']),
            $this->field('sun_hours', 'Peak Sun Hours', 'number', ['min' => 1, 'max' => 8, 'step' => 0.01, 'default' => 4.5]),
            $this->field('panel_watt', 'Panel Wattage', 'number', ['min' => 50, 'max' => 1000000000, 'step' => 0.01, 'default' => 400, 'unit' => 'W']),
            $this->field('system_efficiency', 'System Efficiency', 'number', ['min' => 50, 'max' => 100, 'step' => 0.01, 'default' => 80, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $need = $this->requireNumeric($inputs, 'daily_kwh');
        $sun = $this->requireNumeric($inputs, 'sun_hours');
        $panelW = $this->requireNumeric($inputs, 'panel_watt');
        $eff = $this->requireNumeric($inputs, 'system_efficiency') / 100;
        $requiredKw = $this->safeDivide($need, $sun * $eff);
        $panels = ceil(($requiredKw * 1000) / $panelW);
        return [
            'results' => [
                'required_array_kw' => $this->round($requiredKw, 2),
                'panels_needed' => (int) $panels,
            ],
            'breakdown' => ['daily_kwh' => $need, 'peak_sun_hours' => $sun],
            'units' => ['required_array_kw' => 'kW', 'panels_needed' => 'panels'],
        ];
    }
}
