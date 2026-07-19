<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Mode Calculator
 */
class ModeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'mode_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('values', 'Values (comma-separated)', 'text', ['default' => '1, 2, 2, 3, 3, 3, 4']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_map('strval', array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '')));
        if (count($nums) === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $freq = array_count_values($nums);
        $max = max($freq);
        $modes = array_keys(array_filter($freq, fn ($f) => $f === $max));
        return [
            'results' => ['mode' => implode(', ', $modes), 'frequency' => $max],
            'breakdown' => ['unique_values' => count($freq)],
            'units' => ['mode' => 'value(s)', 'frequency' => 'count'],
        ];
    }
}
