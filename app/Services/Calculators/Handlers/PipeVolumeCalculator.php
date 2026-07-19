<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pipe Volume Calculator
 */
class PipeVolumeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pipe_volume_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('diameter', 'Inner Diameter', 'number', ['min' => 0.001, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.1, 'unit' => 'm']),
            $this->field('length', 'Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $r = $this->requireNumeric($inputs, 'diameter') / 2;
        $length = $this->requireNumeric($inputs, 'length');
        $volume = pi() * ($r ** 2) * $length;
        return [
            'results' => ['volume_m3' => $this->round($volume, 6), 'volume_liters' => $this->round($volume * 1000, 2)],
            'breakdown' => ['radius_m' => $this->round($r, 4)],
            'units' => ['volume_m3' => 'm³', 'volume_liters' => 'L'],
        ];
    }
}
