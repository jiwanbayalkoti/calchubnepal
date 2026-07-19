<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Isolated square footing sizing calculator. Required base area is
 * derived from the column load and the soil's safe bearing capacity
 * (SBC): required_area = load / SBC (kN / kN/m² = m²). If the user
 * supplies a proposed footing size, the actual base pressure is checked
 * against the SBC to determine adequacy.
 */
class FootingCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'footing_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('column_load', 'Column Load', 'number', ['unit' => 'kN', 'min' => 1, 'max' => 1000000, 'step' => 0.1, 'default' => 500]),
            $this->field('safe_bearing_capacity', 'Safe Bearing Capacity', 'number', ['unit' => 'kPa', 'min' => 10, 'max' => 1000, 'step' => 1, 'default' => 150]),
            $this->field('footing_length', 'Proposed Footing Length', 'number', ['unit' => 'm', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('footing_width', 'Proposed Footing Width', 'number', ['unit' => 'm', 'min' => 0, 'max' => 50, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $load = $this->requireNumeric($inputs, 'column_load');
        $sbc = $this->requireNumeric($inputs, 'safe_bearing_capacity');
        $proposedLength = $this->toFloat($inputs, 'footing_length', 0);
        $proposedWidth = $this->toFloat($inputs, 'footing_width', 0);

        $requiredArea = $this->safeDivide($load, $sbc);
        $squareSide = sqrt($requiredArea);

        $results = [
            'required_area' => $this->round($requiredArea),
            'square_footing_side' => $this->round($squareSide),
        ];

        $breakdown = [
            'column_load' => $this->round($load),
            'safe_bearing_capacity' => $sbc,
        ];

        if ($proposedLength > 0 && $proposedWidth > 0) {
            $providedArea = $proposedLength * $proposedWidth;
            $basePressure = $this->safeDivide($load, $providedArea);
            $isSafe = $basePressure <= $sbc;

            $results['base_pressure'] = $this->round($basePressure);
            $results['status'] = $isSafe ? 'Safe' : 'Unsafe';

            $breakdown['provided_area'] = $this->round($providedArea);
        }

        return [
            'results' => $results,
            'breakdown' => $breakdown,
            'units' => [
                'required_area' => 'm²',
                'square_footing_side' => 'm',
                'base_pressure' => 'kPa',
            ],
        ];
    }
}
