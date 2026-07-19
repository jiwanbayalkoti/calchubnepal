<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Simply-supported beam under a uniformly distributed load (UDL).
 * Max bending moment: M = wL²/8
 * Max shear force:    V = wL/2
 * Max deflection:      δ = 5wL⁴ / (384EI)
 * Note: 1 kN/m of UDL is numerically equal to 1 N/mm, which keeps the
 * deflection formula's units consistent when L is expressed in mm,
 * E in N/mm² (MPa) and I in mm⁴.
 */
class BeamCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'beam_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('span_length', 'Span Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 100, 'step' => 0.01, 'default' => 5]),
            $this->field('udl', 'Uniformly Distributed Load', 'number', ['unit' => 'kN/m', 'min' => 0, 'max' => 10000, 'step' => 0.01, 'default' => 10]),
            $this->field('modulus_of_elasticity', 'Modulus of Elasticity', 'number', ['unit' => 'N/mm²', 'min' => 1000, 'max' => 500000, 'step' => 1, 'default' => 200000, 'required' => false]),
            $this->field('moment_of_inertia', 'Moment of Inertia', 'number', ['unit' => 'mm⁴', 'min' => 1, 'max' => 1000000000000, 'step' => 1, 'default' => 80000000, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $spanM = $this->requireNumeric($inputs, 'span_length');
        $udlKnPerM = $this->requireNumeric($inputs, 'udl');
        $elasticModulus = $this->toFloat($inputs, 'modulus_of_elasticity', 200000);
        $momentOfInertia = $this->toFloat($inputs, 'moment_of_inertia', 80000000);

        $maxMoment = ($udlKnPerM * ($spanM ** 2)) / 8;
        $maxShear = ($udlKnPerM * $spanM) / 2;

        $spanMm = $spanM * 1000;
        $wNPerMm = $udlKnPerM;
        $deflectionMm = (5 * $wNPerMm * ($spanMm ** 4)) / (384 * $elasticModulus * $momentOfInertia);

        return [
            'results' => [
                'max_bending_moment' => $this->round($maxMoment),
                'max_shear_force' => $this->round($maxShear),
                'max_deflection' => $this->round($deflectionMm, 3),
            ],
            'breakdown' => [
                'span_mm' => $spanMm,
                'elastic_modulus' => $elasticModulus,
                'moment_of_inertia' => $momentOfInertia,
            ],
            'units' => [
                'max_bending_moment' => 'kN·m',
                'max_shear_force' => 'kN',
                'max_deflection' => 'mm',
            ],
        ];
    }
}
