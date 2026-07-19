<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Nepal Land Measurement
 */
class LandMeasurementNepalCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'land_measurement_nepal_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('ropani', 'Ropani', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 1, 'required' => false]),
            $this->field('aana', 'Aana', 'number', ['min' => 0, 'max' => 15, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('paisa', 'Paisa', 'number', ['min' => 0, 'max' => 3, 'step' => 0.01, 'default' => 0, 'required' => false]),
            $this->field('daam', 'Daam', 'number', ['min' => 0, 'max' => 3, 'step' => 0.01, 'default' => 0, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        // 1 Ropani = 16 Aana = 64 Paisa = 256 Daam = 5476 sq.ft
        $ropani = $this->toFloat($inputs, 'ropani')
            + $this->toFloat($inputs, 'aana') / 16
            + $this->toFloat($inputs, 'paisa') / 64
            + $this->toFloat($inputs, 'daam') / 256;
        $sqft = $ropani * 5476;
        return [
            'results' => [
                'total_ropani' => $this->round($ropani, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 2),
                'hectares' => $this->round($sqft * 0.092903 / 10000, 6),
            ],
            'breakdown' => ['system' => 'Hill system (Ropani–Aana–Paisa–Daam)'],
            'units' => ['total_ropani' => 'ropani', 'square_feet' => 'sq.ft', 'square_meters' => 'm²', 'hectares' => 'ha'],
        ];
    }
}
