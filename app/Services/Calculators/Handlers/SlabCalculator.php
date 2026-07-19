<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * RCC slab material estimator: computes concrete volume from slab
 * dimensions and estimates reinforcement steel weight using a typical
 * steel-to-concrete-volume percentage (commonly 0.7%-1% for slabs).
 * Also reports the one-way simply-supported max bending moment per
 * meter width under self-weight + live load UDL: M = wL²/8.
 */
class SlabCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'slab_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Slab Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 100, 'step' => 0.01, 'default' => 5]),
            $this->field('width', 'Slab Width', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 100, 'step' => 0.01, 'default' => 4]),
            $this->field('thickness', 'Slab Thickness', 'number', ['unit' => 'mm', 'min' => 50, 'max' => 500, 'step' => 1, 'default' => 150]),
            $this->field('steel_percent', 'Steel Reinforcement', 'number', ['unit' => '%', 'min' => 0.3, 'max' => 3, 'step' => 0.05, 'default' => 0.8, 'required' => false]),
            $this->field('udl', 'Total Design Load', 'number', ['unit' => 'kN/m²', 'min' => 0, 'max' => 100, 'step' => 0.1, 'default' => 5, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $width = $this->requireNumeric($inputs, 'width');
        $thicknessMm = $this->requireNumeric($inputs, 'thickness');
        $steelPercent = $this->toFloat($inputs, 'steel_percent', 0.8);
        $udl = $this->toFloat($inputs, 'udl', 5);

        $concreteVolume = $length * $width * ($thicknessMm / 1000);
        $steelVolume = $concreteVolume * ($steelPercent / 100);
        $steelWeightKg = $steelVolume * 7850;

        $shorterSpan = min($length, $width);
        $maxMomentPerMeterWidth = ($udl * ($shorterSpan ** 2)) / 8;

        return [
            'results' => [
                'concrete_volume' => $this->round($concreteVolume),
                'steel_weight' => $this->round($steelWeightKg),
                'max_bending_moment' => $this->round($maxMomentPerMeterWidth),
            ],
            'breakdown' => [
                'steel_volume' => $this->round($steelVolume, 4),
                'design_span' => $this->round($shorterSpan),
            ],
            'units' => [
                'concrete_volume' => 'm³',
                'steel_weight' => 'kg',
                'max_bending_moment' => 'kN·m/m',
            ],
        ];
    }
}
