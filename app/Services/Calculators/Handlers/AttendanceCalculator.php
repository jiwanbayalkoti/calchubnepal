<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Attendance Calculator
 */
class AttendanceCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'attendance_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('classes_held', 'Classes Held', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 50]),
            $this->field('classes_attended', 'Classes Attended', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 1, 'default' => 40]),
            $this->field('required_percent', 'Required %', 'number', ['min' => 1, 'max' => 100, 'step' => 0.01, 'default' => 75, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $held = $this->requireNumeric($inputs, 'classes_held');
        $attended = $this->requireNumeric($inputs, 'classes_attended');
        $req = $this->requireNumeric($inputs, 'required_percent');
        $current = $this->percentageOf($attended, $held);
        $needTotal = ceil($held * $req / 100);
        $needMore = max(0, $needTotal - $attended);
        return [
            'results' => [
                'current_attendance' => $this->round($current, 2),
                'classes_needed_more' => (int) $needMore,
            ],
            'breakdown' => ['required_percent' => $req],
            'units' => ['current_attendance' => '%', 'classes_needed_more' => 'classes'],
        ];
    }
}
