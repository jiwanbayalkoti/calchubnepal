<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Curtain Length Calculator
 */
class CurtainLengthCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'curtain_length_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('window_height', 'Window Height', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 60, 'unit' => 'in']),
            $this->field('extra_drop', 'Extra Drop / Hem', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 8, 'unit' => 'in']),
            $this->field('window_width', 'Window Width', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 48, 'unit' => 'in']),
            $this->field('fullness', 'Fullness Factor', 'number', ['min' => 1, 'max' => 3, 'step' => 0.1, 'default' => 2]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'window_height') + $this->requireNumeric($inputs, 'extra_drop');
        $width = $this->requireNumeric($inputs, 'window_width') * $this->requireNumeric($inputs, 'fullness');
        return [
            'results' => [
                'curtain_length_in' => $this->round($length, 1),
                'fabric_width_in' => $this->round($width, 1),
            ],
            'breakdown' => ['fullness' => $this->requireNumeric($inputs, 'fullness')],
            'units' => ['curtain_length_in' => 'in', 'fabric_width_in' => 'in'],
        ];
    }
}
