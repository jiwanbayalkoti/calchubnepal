<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Battery Backup Calculator
 */
class BatteryBackupCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'battery_backup_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('load_watts', 'Load', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 500, 'unit' => 'W']),
            $this->field('backup_hours', 'Backup Hours', 'number', ['min' => 0.5, 'max' => 1000000000, 'step' => 0.01, 'default' => 4]),
            $this->field('battery_voltage', 'Battery Voltage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'unit' => 'V']),
            $this->field('dod', 'Depth of Discharge', 'number', ['min' => 20, 'max' => 100, 'step' => 0.01, 'default' => 50, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $wh = $this->requireNumeric($inputs, 'load_watts') * $this->requireNumeric($inputs, 'backup_hours');
        $v = $this->requireNumeric($inputs, 'battery_voltage');
        $dod = $this->requireNumeric($inputs, 'dod') / 100;
        $ah = $this->safeDivide($wh, $v * $dod);
        return [
            'results' => [
                'energy_wh' => $this->round($wh),
                'battery_ah_required' => $this->round($ah, 1),
            ],
            'breakdown' => ['formula' => 'Ah = (W × h) / (V × DoD)'],
            'units' => ['energy_wh' => 'Wh', 'battery_ah_required' => 'Ah'],
        ];
    }
}
