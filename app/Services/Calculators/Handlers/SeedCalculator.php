<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Seed Calculator
 */
class SeedCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'seed_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('area', 'Area', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 1, 'unit' => 'hectare']),
            $this->field('seed_rate', 'Seed Rate', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 80, 'unit' => 'kg/ha']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $seed = $this->requireNumeric($inputs, 'area') * $this->requireNumeric($inputs, 'seed_rate');
        return [
            'results' => ['seed_required_kg' => $this->round($seed, 2)],
            'breakdown' => ['formula' => 'area × seed rate'],
            'units' => ['seed_required_kg' => 'kg'],
        ];
    }
}
