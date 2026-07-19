<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Grade Point Average calculator using the standard credit-weighted
 * average: GPA = Σ(credits * grade_points) / Σ(credits).
 * Accepts either a letter grade (mapped to the 4.0 scale) or an explicit
 * numeric grade_points value for each course.
 */
class GpaCalculator extends AbstractCalculatorHandler
{
    protected const GRADE_POINTS = [
        'A+' => 4.0, 'A' => 4.0, 'A-' => 3.7,
        'B+' => 3.3, 'B' => 3.0, 'B-' => 2.7,
        'C+' => 2.3, 'C' => 2.0, 'C-' => 1.7,
        'D+' => 1.3, 'D' => 1.0, 'D-' => 0.7,
        'F' => 0.0,
    ];

    public function key(): string
    {
        return 'gpa_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('courses', 'Courses', 'array', [
                'required' => true,
                'item_schema' => [
                    ['name' => 'name', 'label' => 'Course Name', 'type' => 'text', 'required' => false],
                    ['name' => 'credits', 'label' => 'Credit Hours', 'type' => 'number', 'min' => 0.5, 'max' => 12, 'step' => 0.5, 'required' => true],
                    ['name' => 'grade', 'label' => 'Grade', 'type' => 'select', 'options' => array_keys(self::GRADE_POINTS), 'required' => false],
                    ['name' => 'grade_points', 'label' => 'Grade Points', 'type' => 'number', 'min' => 0, 'max' => 4, 'step' => 0.01, 'required' => false],
                ],
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $courses = $this->toArray($inputs, 'courses', []);

        if (empty($courses)) {
            throw new InvalidArgumentException('At least one course with credits and a grade is required.');
        }

        $totalCredits = 0.0;
        $totalPoints = 0.0;
        $courseBreakdown = [];

        foreach ($courses as $course) {
            $credits = (float) ($course['credits'] ?? 0);

            if ($credits <= 0) {
                continue;
            }

            $gradePoints = isset($course['grade_points']) && $course['grade_points'] !== ''
                ? (float) $course['grade_points']
                : (self::GRADE_POINTS[strtoupper((string) ($course['grade'] ?? ''))] ?? 0.0);

            $totalCredits += $credits;
            $totalPoints += $credits * $gradePoints;

            $courseBreakdown[] = [
                'name' => $course['name'] ?? null,
                'credits' => $credits,
                'grade_points' => $gradePoints,
                'quality_points' => $this->round($credits * $gradePoints, 2),
            ];
        }

        $gpa = $this->safeDivide($totalPoints, $totalCredits);

        return [
            'results' => [
                'gpa' => $this->round($gpa, 2),
                'total_credits' => $totalCredits,
            ],
            'breakdown' => [
                'total_quality_points' => $this->round($totalPoints, 2),
                'courses' => $courseBreakdown,
            ],
            'units' => [
                'gpa' => 'scale 4.0',
                'total_credits' => 'credits',
            ],
        ];
    }
}
