<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Simple Interest Calculator
 */
class SimpleInterestCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'simple_interest_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('principal', 'Principal', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
            $this->field('rate', 'Annual Rate', 'number', ['min' => 0, 'max' => 100, 'step' => 0.01, 'default' => 8, 'unit' => '%']),
            $this->field('time_years', 'Time', 'number', ['min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 2, 'unit' => 'years']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $p = $this->requireNumeric($inputs, 'principal');
        $r = $this->requireNumeric($inputs, 'rate');
        $t = $this->requireNumeric($inputs, 'time_years');
        $interest = $p * $r * $t / 100;
        return [
            'results' => ['interest' => $this->round($interest), 'total_amount' => $this->round($p + $interest)],
            'breakdown' => ['formula' => 'I = P × R × T / 100'],
            'units' => ['interest' => 'currency', 'total_amount' => 'currency'],
        ];
    }
}
