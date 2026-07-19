<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Median Calculator
 */
class MedianCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'median_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('values', 'Values (comma-separated)', 'text', ['default' => '3, 1, 4, 2, 5']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_values(array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '')));
        sort($nums);
        $n = count($nums);
        if ($n === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $median = $n % 2 === 1 ? $nums[intdiv($n, 2)] : (($nums[$n / 2 - 1] + $nums[$n / 2]) / 2);
        return [
            'results' => ['median' => $this->round($median, 6)],
            'breakdown' => ['sorted_values' => implode(', ', $nums), 'count' => $n],
            'units' => ['median' => 'number'],
        ];
    }
}
