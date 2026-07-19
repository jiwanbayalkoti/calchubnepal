<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Pregnancy Due Date Calculator
 */
class PregnancyDueDateCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'pregnancy_due_date_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('lmp_date', 'Last Menstrual Period (LMP)', 'date', ['default' => '2026-06-20']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $lmp = $this->toString($inputs, 'lmp_date');
        $start = \Carbon\Carbon::parse($lmp);
        $due = $start->copy()->addDays(280);
        $today = now()->startOfDay();
        $day = $start->diffInDays($today);
        $week = intdiv(max(0, $day), 7);
        return [
            'results' => [
                'due_date' => $due->toDateString(),
                'current_week' => min(40, $week),
                'days_remaining' => max(0, $today->diffInDays($due, false)),
            ],
            'breakdown' => ['method' => 'Naegele (LMP + 280 days)'],
            'units' => ['due_date' => 'date', 'current_week' => 'weeks', 'days_remaining' => 'days'],
        ];
    }
}
