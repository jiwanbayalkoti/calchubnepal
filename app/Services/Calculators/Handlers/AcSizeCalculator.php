<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * AC Size Calculator
 */
class AcSizeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ac_size_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('room_area', 'Room Area', 'number', ['min' => 50, 'max' => 1000000000, 'step' => 0.01, 'default' => 150, 'unit' => 'sq.ft']),
            $this->field('occupants', 'Occupants', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 2]),
            $this->field('sunlight', 'Sunlight', 'select', ['options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'], 'default' => 'medium']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $area = $this->requireNumeric($inputs, 'room_area');
        $people = $this->requireNumeric($inputs, 'occupants');
        $factor = match ($this->toString($inputs, 'sunlight', 'medium')) {
            'low' => 0.9, 'high' => 1.2, default => 1.0,
        };
        $btu = ($area * 25 + $people * 600) * $factor;
        $tons = $btu / 12000;
        return [
            'results' => [
                'estimated_btu' => $this->round($btu),
                'recommended_tons' => $this->round($tons, 2),
            ],
            'breakdown' => ['note' => 'Rule-of-thumb estimate — verify with HVAC professional'],
            'units' => ['estimated_btu' => 'BTU/h', 'recommended_tons' => 'ton'],
        ];
    }
}
