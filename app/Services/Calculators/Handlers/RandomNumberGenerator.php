<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Random Number Generator
 */
class RandomNumberGenerator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'random_number_generator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('min', 'Minimum', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 1, 'default' => 1]),
            $this->field('max', 'Maximum', 'number', ['min' => -1000000000, 'max' => 1000000000, 'step' => 1, 'default' => 100]),
            $this->field('count', 'How Many', 'number', ['min' => 1, 'max' => 50, 'step' => 1, 'default' => 1]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $min = (int) $this->requireNumeric($inputs, 'min');
        $max = (int) $this->requireNumeric($inputs, 'max');
        $count = (int) $this->requireNumeric($inputs, 'count');
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum cannot be greater than maximum.');
        }
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = random_int($min, $max);
        }
        return [
            'results' => ['random_numbers' => implode(', ', $numbers)],
            'breakdown' => ['range' => "{$min} to {$max}", 'count' => $count],
            'units' => ['random_numbers' => 'integers'],
        ];
    }
}
