<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pregnancy Week Calculator
 */
class PregnancyWeekCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pregnancy_week_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('lmp_date', 'LMP Date', 'date', ['default' => '2026-04-25']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $lmp = \Carbon\Carbon::parse($this->toString($inputs, 'lmp_date'));
        $days = max(0, $lmp->diffInDays(now()));
        $weeks = intdiv($days, 7);
        $rem = $days % 7;
        return [
            'results' => [
                'gestational_age' => "{$weeks} weeks + {$rem} days",
                'trimester' => $weeks < 13 ? 1 : ($weeks < 27 ? 2 : 3),
            ],
            'breakdown' => ['days_since_lmp' => $days],
            'units' => ['gestational_age' => 'weeks+days', 'trimester' => '1-3'],
        ];
    }
}
