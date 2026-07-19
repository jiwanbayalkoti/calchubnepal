<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Grade Calculator
 */
class GradeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'grade_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('score', 'Score Obtained', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 85]),
            $this->field('total', 'Total Marks', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 0.01, 'default' => 100]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $score = $this->requireNumeric($inputs, 'score');
        $total = $this->requireNumeric($inputs, 'total');
        $pct = $this->percentageOf($score, $total);
        $letter = match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 70 => 'B',
            $pct >= 60 => 'C',
            $pct >= 50 => 'D',
            default => 'F',
        };
        return [
            'results' => ['percentage' => $this->round($pct, 2), 'letter_grade' => $letter],
            'breakdown' => ['score' => $score, 'total' => $total],
            'units' => ['percentage' => '%', 'letter_grade' => 'grade'],
        ];
    }
}
