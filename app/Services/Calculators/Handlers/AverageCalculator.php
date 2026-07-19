<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Average / Mean Calculator
 */
class AverageCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'average_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('values', 'Values (comma-separated)', 'text', ['default' => '10, 20, 30, 40']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $raw = $this->toString($inputs, 'values', '');
        $parts = array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '');
        if (count($parts) === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $nums = array_map('floatval', $parts);
        $count = count($nums);
        $sum = array_sum($nums);
        $avg = $sum / $count;
        return [
            'results' => ['average' => $this->round($avg, 6), 'sum' => $this->round($sum, 6), 'count' => $count],
            'breakdown' => ['values' => implode(', ', $nums)],
            'units' => ['average' => 'number', 'sum' => 'number', 'count' => 'count'],
        ];
    }
}
