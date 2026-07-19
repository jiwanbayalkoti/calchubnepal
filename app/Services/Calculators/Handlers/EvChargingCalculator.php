<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * EV Charging Calculator
 */
class EvChargingCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ev_charging_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('battery_kwh', 'Battery Capacity', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 40, 'unit' => 'kWh']),
            $this->field('charge_from', 'Charge From %', 'number', ['min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 20, 'unit' => '%']),
            $this->field('charge_to', 'Charge To %', 'number', ['min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 80, 'unit' => '%']),
            $this->field('rate_per_kwh', 'Electricity Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'unit' => 'currency']),
            $this->field('charger_kw', 'Charger Power', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 7, 'unit' => 'kW']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $from = $this->requireNumeric($inputs, 'charge_from');
        $to = $this->requireNumeric($inputs, 'charge_to');
        if ($to <= $from) {
            throw new InvalidArgumentException('Charge-to % must be greater than charge-from %.');
        }
        $kwh = $this->requireNumeric($inputs, 'battery_kwh') * (($to - $from) / 100);
        $cost = $kwh * $this->requireNumeric($inputs, 'rate_per_kwh');
        $hours = $this->safeDivide($kwh, $this->requireNumeric($inputs, 'charger_kw'));
        return [
            'results' => [
                'energy_added_kwh' => $this->round($kwh, 2),
                'charging_cost' => $this->round($cost),
                'approx_time_hours' => $this->round($hours, 2),
            ],
            'breakdown' => ['soc_window' => "{$from}% → {$to}%"],
            'units' => ['energy_added_kwh' => 'kWh', 'charging_cost' => 'currency', 'approx_time_hours' => 'hours'],
        ];
    }
}
