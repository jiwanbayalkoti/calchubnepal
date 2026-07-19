<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Prime Number Checker
 */
class PrimeNumberCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'prime_number_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('number', 'Number', 'number', ['min' => 0, 'max' => 10000000, 'step' => 1, 'default' => 17]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $number = (int) $this->requireNumeric($inputs, 'number');
        $isPrime = $number > 1;
        if ($isPrime) {
            for ($i = 2; $i * $i <= $number; $i++) {
                if ($number % $i === 0) {
                    $isPrime = false;
                    break;
                }
            }
        }
        return [
            'results' => ['is_prime' => $isPrime ? 'Yes' : 'No', 'number' => $number],
            'breakdown' => ['checked_up_to' => (int) sqrt(max($number, 0))],
            'units' => ['is_prime' => 'boolean', 'number' => 'integer'],
        ];
    }
}
