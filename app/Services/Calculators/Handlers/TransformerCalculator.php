<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Transformer Calculator
 */
class TransformerCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'transformer_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('primary_voltage', 'Primary Voltage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 11000, 'unit' => 'V']),
            $this->field('secondary_voltage', 'Secondary Voltage', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 415, 'unit' => 'V']),
            $this->field('primary_turns', 'Primary Turns (optional)', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $vp = $this->requireNumeric($inputs, 'primary_voltage');
        $vs = $this->requireNumeric($inputs, 'secondary_voltage');
        $np = $this->toFloat($inputs, 'primary_turns', 0);
        $ratio = $this->safeDivide($vp, $vs);
        $results = ['turns_ratio' => $this->round($ratio, 4)];
        if ($np > 0) {
            $results['secondary_turns'] = (int) round($np / $ratio);
        }
        return [
            'results' => $results,
            'breakdown' => ['formula' => 'Vp/Vs = Np/Ns'],
            'units' => ['turns_ratio' => 'ratio', 'secondary_turns' => 'turns'],
        ];
    }
}
