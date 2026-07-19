<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Cumulative GPA calculator: aggregates semester-wise GPA and credit
 * hours into a single credit-weighted CGPA, and converts it to an
 * equivalent percentage using the common formula:
 * percentage = (CGPA / scale) * 100.
 */
class CgpaCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'cgpa_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('semesters', 'Semesters', 'array', [
                'required' => true,
                'item_schema' => [
                    ['name' => 'name', 'label' => 'Semester Name', 'type' => 'text', 'required' => false],
                    ['name' => 'gpa', 'label' => 'Semester GPA', 'type' => 'number', 'min' => 0, 'max' => 10, 'step' => 0.01, 'required' => true],
                    ['name' => 'credits', 'label' => 'Credit Hours', 'type' => 'number', 'min' => 0.5, 'max' => 60, 'step' => 0.5, 'required' => true],
                ],
            ]),
            $this->field('scale', 'Grading Scale', 'number', ['min' => 4, 'max' => 10, 'step' => 0.5, 'default' => 10, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $semesters = $this->toArray($inputs, 'semesters', []);
        $scale = $this->toFloat($inputs, 'scale', 10);

        if (empty($semesters)) {
            throw new InvalidArgumentException('At least one semester with GPA and credits is required.');
        }

        $totalCredits = 0.0;
        $totalPoints = 0.0;
        $semesterBreakdown = [];

        foreach ($semesters as $semester) {
            $credits = (float) ($semester['credits'] ?? 0);
            $gpa = (float) ($semester['gpa'] ?? 0);

            if ($credits <= 0) {
                continue;
            }

            $totalCredits += $credits;
            $totalPoints += $credits * $gpa;

            $semesterBreakdown[] = [
                'name' => $semester['name'] ?? null,
                'gpa' => $gpa,
                'credits' => $credits,
            ];
        }

        $cgpa = $this->safeDivide($totalPoints, $totalCredits);
        $percentageEquivalent = $this->safeDivide($cgpa, $scale) * 100;

        return [
            'results' => [
                'cgpa' => $this->round($cgpa, 2),
                'percentage_equivalent' => $this->round($percentageEquivalent),
            ],
            'breakdown' => [
                'total_credits' => $totalCredits,
                'scale' => $scale,
                'semesters' => $semesterBreakdown,
            ],
            'units' => [
                'cgpa' => 'scale '.$scale,
                'percentage_equivalent' => '%',
            ],
        ];
    }
}
