<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Leap Year Checker
 */
class LeapYearCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'leap_year_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('year', 'Year', 'number', ['min' => 1, 'max' => 9999, 'step' => 1, 'default' => 2026]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $year = (int) $this->requireNumeric($inputs, 'year');
        $isLeap = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
        return [
            'results' => ['is_leap_year' => $isLeap ? 'Yes' : 'No', 'days_in_year' => $isLeap ? 366 : 365],
            'breakdown' => ['year' => $year],
            'units' => ['is_leap_year' => 'boolean', 'days_in_year' => 'days'],
        ];
    }
}
