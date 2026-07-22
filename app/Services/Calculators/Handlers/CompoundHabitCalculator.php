<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Compound Habit Calculator
 * Daily improvement compounding (Atomic Habits 1%/day ≈ 37.8×) + habit-unit framing.
 */
class CompoundHabitCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'compound_habit_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('habit', 'Habit Type', 'select', [
                'options' => [
                    'reading' => 'Reading (hours → books)',
                    'writing' => 'Writing (words → novels)',
                    'language' => 'Language (minutes → CEFR)',
                    'exercise' => 'Exercise (sessions → fitness units)',
                    'coding' => 'Coding (hours → projects)',
                    'custom' => 'Custom / general improvement',
                ],
                'default' => 'reading',
            ]),
            $this->field('daily_improvement_pct', 'Daily Improvement Rate', 'number', ['min' => 0.01, 'max' => 5, 'step' => 0.01, 'default' => 1, 'unit' => '%/day']),
            $this->field('baseline_daily', 'Current Daily Baseline', 'number', ['min' => 0, 'max' => 100000, 'step' => 0.1, 'default' => 0.5]),
            $this->field('baseline_unit', 'Baseline Unit Label', 'string', ['default' => 'hours/day', 'required' => false]),
            $this->field('horizon_years', 'Primary Horizon', 'number', ['min' => 0.25, 'max' => 20, 'step' => 0.25, 'default' => 1, 'unit' => 'years']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $habit = $this->toString($inputs, 'habit', 'reading');
        $dailyPct = $this->requireNumeric($inputs, 'daily_improvement_pct') / 100;
        $baseline = $this->requireNumeric($inputs, 'baseline_daily');
        $unit = $this->toString($inputs, 'baseline_unit', 'units/day');
        $horizonYears = $this->requireNumeric($inputs, 'horizon_years');

        $days1 = 365;
        $days5 = 365 * 5;
        $days10 = 365 * 10;
        $daysH = (int) max(1, round($horizonYears * 365));

        $mult1 = (1 + $dailyPct) ** $days1;
        $mult5 = (1 + $dailyPct) ** $days5;
        $mult10 = (1 + $dailyPct) ** $days10;
        $multH = (1 + $dailyPct) ** $daysH;

        // Cumulative output assuming baseline grows with compounding skill/output rate
        $cumul = static function (float $base, float $pct, int $days): float {
            if ($pct == 0.0) {
                return $base * $days;
            }
            $sum = 0.0;
            $level = $base;
            for ($d = 0; $d < $days; $d++) {
                $sum += $level;
                $level *= (1 + $pct);
            }

            return $sum;
        };

        $c1 = $cumul($baseline, $dailyPct, $days1);
        $c5 = $cumul($baseline, $dailyPct, $days5);
        $c10 = $cumul($baseline, $dailyPct, $days10);
        $cH = $cumul($baseline, $dailyPct, $daysH);

        $frame = $this->habitFrame($habit, $c1, $c5, $c10, $cH);

        return [
            'results' => [
                'compounding_multiplier_1yr' => $this->round($mult1, 1),
                'compounding_multiplier_horizon' => $this->round($multH, 1),
                'atomic_habits_note' => $dailyPct == 0.01
                    ? 'Matches the classic Atomic Habits framing: 1%/day ≈ 37.8× in one year'
                    : sprintf('Your %.2f%%/day compounds to ≈%.1f× in one year (1%%/day ≈ 37.8×)', $dailyPct * 100, $mult1),
                'trajectory_1yr_multiplier' => $this->round($mult1, 2),
                'trajectory_5yr_multiplier' => $this->round($mult5, 1),
                'trajectory_10yr_multiplier' => $this->round($mult10, 1),
                'habit_framing' => $frame,
            ],
            'breakdown' => [
                'cumulative_1yr' => $this->round($c1, 1),
                'cumulative_5yr' => $this->round($c5, 1),
                'cumulative_10yr' => $this->round($c10, 1),
                'baseline_unit' => $unit,
                'formula' => 'multiplier = (1 + r)^days; cumulative = Σ baseline×(1+r)^d over the horizon',
            ],
            'units' => [
                'compounding_multiplier_1yr' => '×',
                'compounding_multiplier_horizon' => '×',
                'atomic_habits_note' => '',
                'trajectory_1yr_multiplier' => '×',
                'trajectory_5yr_multiplier' => '×',
                'trajectory_10yr_multiplier' => '×',
                'habit_framing' => '',
            ],
        ];
    }

    protected function habitFrame(string $habit, float $c1, float $c5, float $c10, float $cH): string
    {
        return match ($habit) {
            'reading' => sprintf(
                '≈%.0f / %.0f / %.0f reading-hours → roughly %.0f / %.0f / %.0f books (at 8 hrs/book) over 1 / 5 / 10 yrs',
                $c1, $c5, $c10, $c1 / 8, $c5 / 8, $c10 / 8
            ),
            'writing' => sprintf(
                '≈%.0f / %.0f / %.0f words → roughly %.1f / %.1f / %.1f novels (80k words) over 1 / 5 / 10 yrs',
                $c1, $c5, $c10, $c1 / 80000, $c5 / 80000, $c10 / 80000
            ),
            'language' => sprintf(
                '≈%.0f / %.0f / %.0f study-minutes → CEFR path: ~A2 in %.0f hrs, B1≈%.0f, B2≈%.0f (illustrative)',
                $c1, $c5, $c10, 200, 400, 600
            ),
            'exercise' => sprintf('≈%.0f / %.0f / %.0f sessions-equivalent over 1 / 5 / 10 yrs (compounding capacity)', $c1, $c5, $c10),
            'coding' => sprintf('≈%.0f / %.0f / %.0f focused hours → roughly %.0f / %.0f / %.0f small projects (40 hrs each)', $c1, $c5, $c10, $c1 / 40, $c5 / 40, $c10 / 40),
            default => sprintf('Horizon cumulative output ≈ %.1f baseline-units (1yr %.1f · 5yr %.1f · 10yr %.1f)', $cH, $c1, $c5, $c10),
        };
    }
}
