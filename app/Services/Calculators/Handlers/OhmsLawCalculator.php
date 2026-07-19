<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Ohm's Law Calculator
 */
class OhmsLawCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ohms_law_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('solve_for', 'Solve For', 'select', ['options' => ['voltage' => 'Voltage (V)', 'current' => 'Current (I)', 'resistance' => 'Resistance (R)', 'power' => 'Power (P)'], 'default' => 'voltage']),
            $this->field('voltage', 'Voltage (V)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'required' => false]),
            $this->field('current', 'Current (A)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'required' => false]),
            $this->field('resistance', 'Resistance (Ω)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 6, 'required' => false]),
            $this->field('power', 'Power (W)', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 24, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $mode = $this->toString($inputs, 'solve_for', 'voltage');
        $v = $this->toFloat($inputs, 'voltage');
        $i = $this->toFloat($inputs, 'current');
        $r = $this->toFloat($inputs, 'resistance');
        $p = $this->toFloat($inputs, 'power');
        $result = match ($mode) {
            'current' => $this->safeDivide($v, $r),
            'resistance' => $this->safeDivide($v, $i),
            'power' => $v * $i,
            default => $i * $r,
        };
        $label = match ($mode) {
            'current' => 'current_a',
            'resistance' => 'resistance_ohm',
            'power' => 'power_w',
            default => 'voltage_v',
        };
        return [
            'results' => [$label => $this->round($result, 4)],
            'breakdown' => ['solve_for' => $mode],
            'units' => [$label => match ($mode) { 'current' => 'A', 'resistance' => 'Ω', 'power' => 'W', default => 'V' }],
        ];
    }
}
