<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Stair Calculator
 */
class StairCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'stair_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('total_rise', 'Total Rise (Height)', 'number', ['min' => 0.5, 'max' => 1000000000, 'step' => 0.01, 'default' => 3, 'unit' => 'm']),
            $this->field('riser_height', 'Preferred Riser Height', 'number', ['min' => 0.1, 'max' => 0.2, 'step' => 0.01, 'default' => 0.15, 'unit' => 'm']),
            $this->field('tread_depth', 'Tread Depth', 'number', ['min' => 0.2, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.28, 'unit' => 'm']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $rise = $this->requireNumeric($inputs, 'total_rise');
        $riser = $this->requireNumeric($inputs, 'riser_height');
        $tread = $this->requireNumeric($inputs, 'tread_depth');
        $risers = (int) max(1, round($rise / $riser));
        $actualRiser = $rise / $risers;
        $treads = max(1, $risers - 1);
        $run = $treads * $tread;
        return [
            'results' => [
                'number_of_risers' => $risers,
                'actual_riser_height' => $this->round($actualRiser, 3),
                'number_of_treads' => $treads,
                'total_run' => $this->round($run, 3),
            ],
            'breakdown' => ['rule_of_thumb' => '2R + T ≈ 0.60–0.65 m'],
            'units' => ['number_of_risers' => 'count', 'actual_riser_height' => 'm', 'number_of_treads' => 'count', 'total_run' => 'm'],
        ];
    }
}
