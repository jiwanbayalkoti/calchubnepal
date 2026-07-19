<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Business Days Calculator
 */
class BusinessDaysCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'business_days_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('start_date', 'Start Date', 'date', ['default' => '2026-07-18']),
            $this->field('business_days', 'Business Days To Add', 'number', ['min' => 1, 'max' => 365, 'step' => 1, 'default' => 10]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $date = \Carbon\Carbon::parse($this->toString($inputs, 'start_date'))->startOfDay();
        $need = (int) $this->requireNumeric($inputs, 'business_days');
        $added = 0;
        while ($added < $need) {
            $date->addDay();
            if (! $date->isWeekend()) {
                $added++;
            }
        }
        return [
            'results' => ['result_date' => $date->toDateString()],
            'breakdown' => ['business_days_added' => $need],
            'units' => ['result_date' => 'date'],
        ];
    }
}
