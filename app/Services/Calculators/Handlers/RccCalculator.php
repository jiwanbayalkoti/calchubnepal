<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * RCC Calculator
 */
class RccCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rcc_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5, 'unit' => 'm']),
            $this->field('width', 'Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.3, 'unit' => 'm']),
            $this->field('depth', 'Depth', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.45, 'unit' => 'm']),
            $this->field('mix', 'Mix', 'select', ['options' => ['1:1.5:3' => 'M20 (1:1.5:3)', '1:1:2' => 'M25 (1:1:2)', '1:2:4' => 'M15 (1:2:4)'], 'default' => '1:1.5:3']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $volume = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'depth');
        $wet = $volume * 1.54;
        $parts = array_map('floatval', explode(':', $this->toString($inputs, 'mix', '1:1.5:3')));
        $sum = array_sum($parts);
        $cementM3 = $wet * $parts[0] / $sum;
        return [
            'results' => [
                'concrete_volume_m3' => $this->round($volume, 3),
                'cement_bags' => $this->round($cementM3 / 0.035, 1),
                'sand_m3' => $this->round($wet * $parts[1] / $sum, 3),
                'aggregate_m3' => $this->round($wet * $parts[2] / $sum, 3),
            ],
            'breakdown' => ['wet_volume' => $this->round($wet, 3)],
            'units' => ['concrete_volume_m3' => 'm³', 'cement_bags' => 'bags', 'sand_m3' => 'm³', 'aggregate_m3' => 'm³'],
        ];
    }
}
