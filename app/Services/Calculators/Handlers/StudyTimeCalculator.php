<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Study Time Calculator
 */
class StudyTimeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'study_time_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('pages', 'Pages To Study', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 40]),
            $this->field('pages_per_hour', 'Pages Per Hour', 'number', ['min' => 0.5, 'max' => 1000000000, 'step' => 0.01, 'default' => 8]),
            $this->field('days_available', 'Days Available', 'number', ['min' => 1, 'max' => 1000000000, 'step' => 1, 'default' => 5]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $pages = $this->requireNumeric($inputs, 'pages');
        $pph = $this->requireNumeric($inputs, 'pages_per_hour');
        $days = $this->requireNumeric($inputs, 'days_available');
        $hours = $this->safeDivide($pages, $pph);
        return [
            'results' => [
                'total_study_hours' => $this->round($hours, 2),
                'hours_per_day' => $this->round($this->safeDivide($hours, $days), 2),
            ],
            'breakdown' => ['pages' => $pages],
            'units' => ['total_study_hours' => 'hours', 'hours_per_day' => 'hours/day'],
        ];
    }
}
