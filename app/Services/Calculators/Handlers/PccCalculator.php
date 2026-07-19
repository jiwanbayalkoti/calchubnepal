<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * PCC Calculator
 */
class PccCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pcc_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'm']),
            $this->field('width', 'Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'm']),
            $this->field('thickness', 'Thickness', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.1, 'unit' => 'm']),
            $this->field('mix', 'Mix Ratio', 'select', ['options' => ['1:3:6' => '1:3:6', '1:4:8' => '1:4:8', '1:5:10' => '1:5:10'], 'default' => '1:4:8']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $volume = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'thickness');
        $wet = $volume * 1.52;
        [$c, $s, $a] = array_map('intval', explode(':', $this->toString($inputs, 'mix', '1:4:8')));
        $parts = $c + $s + $a;
        $cementM3 = $wet * $c / $parts;
        $bags = $cementM3 / 0.035;
        return [
            'results' => [
                'volume_m3' => $this->round($volume, 3),
                'cement_bags' => $this->round($bags, 1),
                'sand_m3' => $this->round($wet * $s / $parts, 3),
                'aggregate_m3' => $this->round($wet * $a / $parts, 3),
            ],
            'breakdown' => ['wet_volume' => $this->round($wet, 3), 'mix' => "{$c}:{$s}:{$a}"],
            'units' => ['volume_m3' => 'm³', 'cement_bags' => 'bags', 'sand_m3' => 'm³', 'aggregate_m3' => 'm³'],
        ];
    }
}
