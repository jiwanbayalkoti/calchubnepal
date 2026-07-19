<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * kVA Calculator
 */
class KvaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'kva_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('kw', 'Real Power', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 10, 'unit' => 'kW']),
            $this->field('power_factor', 'Power Factor', 'number', ['min' => 0.1, 'max' => 1, 'step' => 0.01, 'default' => 0.8]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $kw = $this->requireNumeric($inputs, 'kw');
        $pf = $this->requireNumeric($inputs, 'power_factor');
        $kva = $this->safeDivide($kw, $pf);
        return [
            'results' => ['kva' => $this->round($kva, 3), 'kvar_approx' => $this->round(sqrt(max(($kva ** 2) - ($kw ** 2), 0)), 3)],
            'breakdown' => ['formula' => 'kVA = kW / PF'],
            'units' => ['kva' => 'kVA', 'kvar_approx' => 'kVAR'],
        ];
    }
}
