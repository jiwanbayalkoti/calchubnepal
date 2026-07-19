<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Ovulation Calculator
 */
class OvulationCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ovulation_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('lmp_date', 'Last Period Start', 'date', ['default' => '2026-07-11']),
            $this->field('cycle_length', 'Average Cycle Length', 'number', ['min' => 21, 'max' => 45, 'step' => 1, 'default' => 28]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $lmp = \Carbon\Carbon::parse($this->toString($inputs, 'lmp_date'));
        $cycle = (int) $this->requireNumeric($inputs, 'cycle_length');
        $ovulation = $lmp->copy()->addDays($cycle - 14);
        $fertileStart = $ovulation->copy()->subDays(5);
        $fertileEnd = $ovulation->copy()->addDay();
        $nextPeriod = $lmp->copy()->addDays($cycle);
        return [
            'results' => [
                'estimated_ovulation' => $ovulation->toDateString(),
                'fertile_window' => $fertileStart->toDateString().' to '.$fertileEnd->toDateString(),
                'next_period' => $nextPeriod->toDateString(),
            ],
            'breakdown' => ['cycle_length' => $cycle],
            'units' => ['estimated_ovulation' => 'date', 'fertile_window' => 'dates', 'next_period' => 'date'],
        ];
    }
}
