<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pressure Drop Estimator
 */
class PressureDropCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pressure_drop_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('friction_factor', 'Friction Factor f', 'number', ['min' => 0.001, 'max' => 0.1, 'step' => 0.001, 'default' => 0.02]),
            $this->field('length', 'Pipe Length', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 50, 'unit' => 'm']),
            $this->field('diameter', 'Diameter', 'number', ['min' => 0.001, 'max' => 1000000000, 'step' => 0.01, 'default' => 0.05, 'unit' => 'm']),
            $this->field('velocity', 'Velocity', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'unit' => 'm/s']),
            $this->field('density', 'Fluid Density', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1000, 'unit' => 'kg/m³']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // Darcy-Weisbach: ΔP = f * (L/D) * (ρ v² / 2)
        $f = $this->requireNumeric($inputs, 'friction_factor');
        $L = $this->requireNumeric($inputs, 'length');
        $D = $this->requireNumeric($inputs, 'diameter');
        $v = $this->requireNumeric($inputs, 'velocity');
        $rho = $this->requireNumeric($inputs, 'density');
        $dp = $f * ($L / $D) * ($rho * ($v ** 2) / 2);
        return [
            'results' => ['pressure_drop_pa' => $this->round($dp), 'pressure_drop_bar' => $this->round($dp / 100000, 4)],
            'breakdown' => ['formula' => 'Darcy–Weisbach'],
            'units' => ['pressure_drop_pa' => 'Pa', 'pressure_drop_bar' => 'bar'],
        ];
    }
}
