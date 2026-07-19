<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Calculates excavation (cut) volume for a foundation trench/pit and the
 * loose (bulked) soil volume to be disposed of or backfilled.
 */
class ExcavationCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'excavation_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('length', 'Length', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 10]),
            $this->field('width', 'Width', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 1000, 'step' => 0.01, 'default' => 2]),
            $this->field('depth', 'Depth', 'number', ['unit' => 'm', 'min' => 0.1, 'max' => 50, 'step' => 0.01, 'default' => 1.5]),
            $this->field('bulking_factor', 'Soil Bulking Factor', 'number', ['min' => 1, 'max' => 2, 'step' => 0.01, 'default' => 1.2, 'required' => false]),
            $this->field('rate_per_m3', 'Excavation Rate per m³', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 100000, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $width = $this->requireNumeric($inputs, 'width');
        $depth = $this->requireNumeric($inputs, 'depth');
        $bulkingFactor = $this->toFloat($inputs, 'bulking_factor', 1.2);
        $ratePerM3 = $this->toFloat($inputs, 'rate_per_m3', 0);

        $excavationVolume = $length * $width * $depth;
        $bulkedVolume = $excavationVolume * $bulkingFactor;
        $estimatedCost = $excavationVolume * $ratePerM3;

        return [
            'results' => [
                'excavation_volume' => $this->round($excavationVolume),
                'bulked_soil_volume' => $this->round($bulkedVolume),
                'estimated_cost' => $this->round($estimatedCost),
            ],
            'breakdown' => [
                'length' => $length,
                'width' => $width,
                'depth' => $depth,
                'bulking_factor' => $bulkingFactor,
            ],
            'units' => [
                'excavation_volume' => 'm³',
                'bulked_soil_volume' => 'm³',
                'estimated_cost' => 'currency',
            ],
        ];
    }
}
