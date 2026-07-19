<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Variance Calculator
 */
class VarianceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'variance_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('values', 'Values (comma-separated)', 'text', ['default' => '4, 8, 6, 5, 3, 7']),
            $this->field('type', 'Type', 'select', ['options' => ['sample' => 'Sample (n-1)', 'population' => 'Population (n)'], 'default' => 'sample']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));
        $n = count($nums);
        if ($n < 2) {
            throw new InvalidArgumentException('Enter at least two numbers.');
        }
        $mean = array_sum($nums) / $n;
        $varianceSum = 0.0;
        foreach ($nums as $v) {
            $varianceSum += ($v - $mean) ** 2;
        }
        $divisor = $this->toString($inputs, 'type', 'sample') === 'population' ? $n : ($n - 1);
        $variance = $varianceSum / $divisor;
        return [
            'results' => ['variance' => $this->round($variance, 6), 'mean' => $this->round($mean, 6)],
            'breakdown' => ['count' => $n],
            'units' => ['variance' => 'number', 'mean' => 'number'],
        ];
    }
}
