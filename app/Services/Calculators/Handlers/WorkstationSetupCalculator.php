<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Workstation Setup Cost Calculator
 * Estimates the total cost for setting up a professional home office/workstation.
 * Includes desk, chair, monitors, lighting, storage, and accessories.
 */
class WorkstationSetupCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'workstation_setup_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('setup_type', 'Setup Type', 'select', [
                'options' => [
                    'basic' => 'Basic (Essential items only)',
                    'standard' => 'Standard (Comfortable home office)',
                    'premium' => 'Premium (Professional studio)',
                    'custom' => 'Custom (Select individual items)',
                ],
                'default' => 'standard',
            ]),
            $this->field('desk_type', 'Desk Type', 'select', [
                'options' => [
                    'none' => 'No desk needed',
                    'basic_desk' => 'Basic Desk ($100-200)',
                    'l_shaped' => 'L-Shaped Desk ($200-400)',
                    'standing_desk' => 'Standing/Adjustable Desk ($400-800)',
                    'custom_built' => 'Custom Built Desk ($800-2000)',
                ],
                'default' => 'l_shaped',
                'required' => false,
            ]),
            $this->field('chair_type', 'Chair Type', 'select', [
                'options' => [
                    'none' => 'No chair needed',
                    'basic_chair' => 'Basic Office Chair ($50-150)',
                    'ergonomic' => 'Ergonomic Chair ($200-500)',
                    'premium_ergonomic' => 'Premium Ergonomic ($500-1200)',
                    'executive' => 'Executive Chair ($800-2000)',
                ],
                'default' => 'ergonomic',
                'required' => false,
            ]),
            $this->field('monitor_count', 'Number of Monitors', 'select', [
                'options' => [
                    '0' => 'None (Laptop only)',
                    '1' => 'Single Monitor',
                    '2' => 'Dual Monitors',
                    '3' => 'Triple Monitors',
                ],
                'default' => '2',
                'required' => false,
            ]),
            $this->field('monitor_type', 'Monitor Type', 'select', [
                'options' => [
                    'budget_24' => '24" Budget ($100-200 each)',
                    'standard_27' => '27" Standard ($200-350 each)',
                    'ultrawide' => 'Ultrawide 34"+ ($400-800 each)',
                    'premium_4k' => '4K Premium ($500-1000 each)',
                ],
                'default' => 'standard_27',
                'required' => false,
            ]),
            $this->field('lighting', 'Lighting Setup', 'select', [
                'options' => [
                    'none' => 'No additional lighting',
                    'desk_lamp' => 'Desk Lamp ($30-80)',
                    'led_strip' => 'LED Strip Ambient ($50-150)',
                    'full_ambient' => 'Full Ambient + Task Lighting ($150-400)',
                    'studio' => 'Studio Grade Lighting ($400-1000)',
                ],
                'default' => 'led_strip',
                'required' => false,
            ]),
            $this->field('storage', 'Storage & Organization', 'select', [
                'options' => [
                    'none' => 'No additional storage',
                    'basic_shelf' => 'Basic Shelving ($50-150)',
                    'desk_organizers' => 'Desk Organizers ($30-100)',
                    'filing_cabinet' => 'Filing Cabinet ($100-300)',
                    'full_storage' => 'Full Storage System ($300-800)',
                ],
                'default' => 'basic_shelf',
                'required' => false,
            ]),
            $this->field('peripherals', 'Peripherals', 'select', [
                'options' => [
                    'none' => 'No peripherals needed',
                    'basic' => 'Basic (Keyboard + Mouse) ($50-100)',
                    'ergonomic_peripherals' => 'Ergonomic Set ($150-300)',
                    'premium_peripherals' => 'Premium Mechanical + Mouse ($300-600)',
                ],
                'default' => 'ergonomic_peripherals',
                'required' => false,
            ]),
            $this->field('cable_management', 'Cable Management', 'boolean', [
                'default' => true,
                'required' => false,
            ]),
            $this->field('monitor_arm', 'Monitor Arm/Mount', 'boolean', [
                'default' => true,
                'required' => false,
            ]),
            $this->field('budget_adjustment', 'Budget Preference', 'select', [
                'options' => [
                    'budget' => 'Budget-friendly (Lower range)',
                    'mid' => 'Mid-range (Average)',
                    'quality' => 'Quality-focused (Higher range)',
                ],
                'default' => 'mid',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $setupType = $this->toString($inputs, 'setup_type', 'standard');
        $budgetPref = $this->toString($inputs, 'budget_adjustment', 'mid');
        $cableManagement = $this->toBool($inputs, 'cable_management', true);
        $monitorArm = $this->toBool($inputs, 'monitor_arm', true);

        $budgetMultiplier = match ($budgetPref) {
            'budget' => 0.7,
            'quality' => 1.3,
            default => 1.0,
        };

        if ($setupType !== 'custom') {
            return $this->calculatePreset($setupType, $budgetMultiplier, $cableManagement, $monitorArm);
        }

        $deskType = $this->toString($inputs, 'desk_type', 'l_shaped');
        $chairType = $this->toString($inputs, 'chair_type', 'ergonomic');
        $monitorCount = $this->toInt($inputs, 'monitor_count', 2);
        $monitorType = $this->toString($inputs, 'monitor_type', 'standard_27');
        $lighting = $this->toString($inputs, 'lighting', 'led_strip');
        $storage = $this->toString($inputs, 'storage', 'basic_shelf');
        $peripherals = $this->toString($inputs, 'peripherals', 'ergonomic_peripherals');

        $breakdown = [];
        $totalMin = 0;
        $totalMax = 0;

        $deskCost = $this->getDeskCost($deskType);
        if ($deskCost['min'] > 0) {
            $breakdown['desk'] = $this->formatRange($deskCost, $budgetMultiplier);
            $totalMin += $deskCost['min'] * $budgetMultiplier;
            $totalMax += $deskCost['max'] * $budgetMultiplier;
        }

        $chairCost = $this->getChairCost($chairType);
        if ($chairCost['min'] > 0) {
            $breakdown['chair'] = $this->formatRange($chairCost, $budgetMultiplier);
            $totalMin += $chairCost['min'] * $budgetMultiplier;
            $totalMax += $chairCost['max'] * $budgetMultiplier;
        }

        $monitorCost = $this->getMonitorCost($monitorType);
        if ($monitorCount > 0 && $monitorCost['min'] > 0) {
            $monitorTotalMin = $monitorCost['min'] * $monitorCount;
            $monitorTotalMax = $monitorCost['max'] * $monitorCount;
            $breakdown['monitors'] = $this->formatRange(['min' => $monitorTotalMin, 'max' => $monitorTotalMax], $budgetMultiplier) . " ({$monitorCount}x)";
            $totalMin += $monitorTotalMin * $budgetMultiplier;
            $totalMax += $monitorTotalMax * $budgetMultiplier;

            if ($monitorArm) {
                $armCost = $monitorCount === 1 ? ['min' => 30, 'max' => 80] : ['min' => 50, 'max' => 150];
                $breakdown['monitor_arm'] = $this->formatRange($armCost, $budgetMultiplier);
                $totalMin += $armCost['min'] * $budgetMultiplier;
                $totalMax += $armCost['max'] * $budgetMultiplier;
            }
        }

        $lightingCost = $this->getLightingCost($lighting);
        if ($lightingCost['min'] > 0) {
            $breakdown['lighting'] = $this->formatRange($lightingCost, $budgetMultiplier);
            $totalMin += $lightingCost['min'] * $budgetMultiplier;
            $totalMax += $lightingCost['max'] * $budgetMultiplier;
        }

        $storageCost = $this->getStorageCost($storage);
        if ($storageCost['min'] > 0) {
            $breakdown['storage'] = $this->formatRange($storageCost, $budgetMultiplier);
            $totalMin += $storageCost['min'] * $budgetMultiplier;
            $totalMax += $storageCost['max'] * $budgetMultiplier;
        }

        $peripheralsCost = $this->getPeripheralsCost($peripherals);
        if ($peripheralsCost['min'] > 0) {
            $breakdown['peripherals'] = $this->formatRange($peripheralsCost, $budgetMultiplier);
            $totalMin += $peripheralsCost['min'] * $budgetMultiplier;
            $totalMax += $peripheralsCost['max'] * $budgetMultiplier;
        }

        if ($cableManagement) {
            $cableCost = ['min' => 30, 'max' => 100];
            $breakdown['cable_management'] = $this->formatRange($cableCost, $budgetMultiplier);
            $totalMin += $cableCost['min'] * $budgetMultiplier;
            $totalMax += $cableCost['max'] * $budgetMultiplier;
        }

        $avgCost = ($totalMin + $totalMax) / 2;

        return [
            'results' => [
                'estimated_total_min' => $this->round($totalMin),
                'estimated_total_max' => $this->round($totalMax),
                'estimated_average' => $this->round($avgCost),
                'setup_type' => 'Custom Setup',
            ],
            'breakdown' => $breakdown,
            'units' => [
                'estimated_total_min' => 'currency',
                'estimated_total_max' => 'currency',
                'estimated_average' => 'currency',
            ],
        ];
    }

    private function calculatePreset(string $type, float $multiplier, bool $cableManagement, bool $monitorArm): array
    {
        $presets = [
            'basic' => [
                'description' => 'Essential home office setup',
                'items' => [
                    'desk' => ['Basic Desk', 100, 200],
                    'chair' => ['Basic Office Chair', 50, 150],
                    'monitor' => ['24" Monitor (1x)', 100, 200],
                    'peripherals' => ['Basic Keyboard + Mouse', 50, 100],
                    'lighting' => ['Desk Lamp', 30, 80],
                ],
            ],
            'standard' => [
                'description' => 'Comfortable work-from-home setup',
                'items' => [
                    'desk' => ['L-Shaped Desk', 200, 400],
                    'chair' => ['Ergonomic Chair', 200, 500],
                    'monitors' => ['27" Monitors (2x)', 400, 700],
                    'monitor_arm' => ['Dual Monitor Arm', 50, 150],
                    'peripherals' => ['Ergonomic Keyboard + Mouse', 150, 300],
                    'lighting' => ['LED Ambient Lighting', 50, 150],
                    'storage' => ['Basic Shelving', 50, 150],
                    'cable_management' => ['Cable Management Kit', 30, 80],
                ],
            ],
            'premium' => [
                'description' => 'Professional studio workspace',
                'items' => [
                    'desk' => ['Standing/Adjustable Desk', 400, 800],
                    'chair' => ['Premium Ergonomic Chair', 500, 1200],
                    'monitors' => ['4K Premium Monitors (2x)', 1000, 2000],
                    'monitor_arm' => ['Premium Monitor Arms', 100, 250],
                    'peripherals' => ['Premium Mechanical Keyboard + Mouse', 300, 600],
                    'lighting' => ['Studio Grade Lighting', 400, 1000],
                    'storage' => ['Full Storage System', 300, 800],
                    'cable_management' => ['Premium Cable Management', 80, 200],
                    'accessories' => ['Desk Mat, Webcam, Headset Stand', 150, 400],
                ],
            ],
        ];

        $preset = $presets[$type] ?? $presets['standard'];
        $breakdown = [];
        $totalMin = 0;
        $totalMax = 0;

        foreach ($preset['items'] as $key => $item) {
            if ($key === 'cable_management' && ! $cableManagement) {
                continue;
            }
            if ($key === 'monitor_arm' && ! $monitorArm) {
                continue;
            }

            $adjustedMin = $item[1] * $multiplier;
            $adjustedMax = $item[2] * $multiplier;
            $breakdown[$key] = $item[0] . ': $' . $this->round($adjustedMin, 0) . ' - $' . $this->round($adjustedMax, 0);
            $totalMin += $adjustedMin;
            $totalMax += $adjustedMax;
        }

        $avgCost = ($totalMin + $totalMax) / 2;

        return [
            'results' => [
                'estimated_total_min' => $this->round($totalMin),
                'estimated_total_max' => $this->round($totalMax),
                'estimated_average' => $this->round($avgCost),
                'setup_type' => ucfirst($type) . ' Setup - ' . $preset['description'],
            ],
            'breakdown' => $breakdown,
            'units' => [
                'estimated_total_min' => 'currency',
                'estimated_total_max' => 'currency',
                'estimated_average' => 'currency',
            ],
        ];
    }

    private function getDeskCost(string $type): array
    {
        return match ($type) {
            'basic_desk' => ['min' => 100, 'max' => 200],
            'l_shaped' => ['min' => 200, 'max' => 400],
            'standing_desk' => ['min' => 400, 'max' => 800],
            'custom_built' => ['min' => 800, 'max' => 2000],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function getChairCost(string $type): array
    {
        return match ($type) {
            'basic_chair' => ['min' => 50, 'max' => 150],
            'ergonomic' => ['min' => 200, 'max' => 500],
            'premium_ergonomic' => ['min' => 500, 'max' => 1200],
            'executive' => ['min' => 800, 'max' => 2000],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function getMonitorCost(string $type): array
    {
        return match ($type) {
            'budget_24' => ['min' => 100, 'max' => 200],
            'standard_27' => ['min' => 200, 'max' => 350],
            'ultrawide' => ['min' => 400, 'max' => 800],
            'premium_4k' => ['min' => 500, 'max' => 1000],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function getLightingCost(string $type): array
    {
        return match ($type) {
            'desk_lamp' => ['min' => 30, 'max' => 80],
            'led_strip' => ['min' => 50, 'max' => 150],
            'full_ambient' => ['min' => 150, 'max' => 400],
            'studio' => ['min' => 400, 'max' => 1000],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function getStorageCost(string $type): array
    {
        return match ($type) {
            'basic_shelf' => ['min' => 50, 'max' => 150],
            'desk_organizers' => ['min' => 30, 'max' => 100],
            'filing_cabinet' => ['min' => 100, 'max' => 300],
            'full_storage' => ['min' => 300, 'max' => 800],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function getPeripheralsCost(string $type): array
    {
        return match ($type) {
            'basic' => ['min' => 50, 'max' => 100],
            'ergonomic_peripherals' => ['min' => 150, 'max' => 300],
            'premium_peripherals' => ['min' => 300, 'max' => 600],
            default => ['min' => 0, 'max' => 0],
        };
    }

    private function formatRange(array $cost, float $multiplier): string
    {
        $min = $this->round($cost['min'] * $multiplier, 0);
        $max = $this->round($cost['max'] * $multiplier, 0);

        return '$' . $min . ' - $' . $max;
    }
}
