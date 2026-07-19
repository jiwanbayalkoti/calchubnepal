<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Rebar Calculator
 */
class RebarCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rebar_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Bar Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 12, 'unit' => 'm']),
            $this->field('diameter_mm', 'Diameter', 'number', ['min' => 6, 'max' => 40, 'step' => 0.01, 'default' => 12, 'unit' => 'mm']),
            $this->field('quantity', 'Number of Bars', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $d = $this->requireNumeric($inputs, 'diameter_mm');
        $qty = $this->requireNumeric($inputs, 'quantity');
        // weight kg/m ≈ d²/162
        $kgPerM = ($d ** 2) / 162;
        $totalLength = $length * $qty;
        $weight = $totalLength * $kgPerM;
        return [
            'results' => [
                'weight_per_meter' => $this->round($kgPerM, 3),
                'total_length' => $this->round($totalLength, 2),
                'total_weight' => $this->round($weight, 2),
            ],
            'breakdown' => ['formula' => 'kg/m = d² / 162'],
            'units' => ['weight_per_meter' => 'kg/m', 'total_length' => 'm', 'total_weight' => 'kg'],
        ];
    }
}
