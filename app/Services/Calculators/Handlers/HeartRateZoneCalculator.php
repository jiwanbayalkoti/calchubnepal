<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Heart Rate Zone Calculator
 */
class HeartRateZoneCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'heart_rate_zone_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('age', 'Age', 'number', ['min' => 10, 'max' => 90, 'step' => 1, 'default' => 30]),
            $this->field('resting_hr', 'Resting Heart Rate', 'number', ['min' => 30, 'max' => 120, 'step' => 1, 'default' => 60]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $age = $this->requireNumeric($inputs, 'age');
        $rest = $this->requireNumeric($inputs, 'resting_hr');
        $max = 220 - $age;
        $reserve = $max - $rest;
        $zone = fn ($lo, $hi) => (int) round($rest + $reserve * $lo).'–'.(int) round($rest + $reserve * $hi);
        return [
            'results' => [
                'max_hr' => (int) $max,
                'zone_1_recovery' => $zone(0.5, 0.6),
                'zone_2_fat_burn' => $zone(0.6, 0.7),
                'zone_3_cardio' => $zone(0.7, 0.8),
                'zone_4_peak' => $zone(0.8, 0.9),
                'zone_5_max' => $zone(0.9, 1.0),
            ],
            'breakdown' => ['method' => 'Karvonen'],
            'units' => ['max_hr' => 'bpm', 'zone_1_recovery' => 'bpm', 'zone_2_fat_burn' => 'bpm', 'zone_3_cardio' => 'bpm', 'zone_4_peak' => 'bpm', 'zone_5_max' => 'bpm'],
        ];
    }
}
