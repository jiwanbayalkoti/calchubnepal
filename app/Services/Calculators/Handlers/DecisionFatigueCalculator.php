<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Decision Fatigue Calculator
 * Effective decisions after routine/rest; alcohol + sleep penalties; crash hour.
 */
class DecisionFatigueCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'decision_fatigue_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('daily_decisions', 'Daily Conscious Decisions', 'number', ['min' => 5, 'max' => 300, 'step' => 1, 'default' => 75]),
            $this->field('trivial_pct', 'Share That Are Trivial', 'number', ['min' => 0, 'max' => 100, 'step' => 1, 'default' => 55, 'unit' => '%']),
            $this->field('routine_pct', 'Scheduled / Routinized %', 'number', ['min' => 0, 'max' => 100, 'step' => 1, 'default' => 25, 'unit' => '%']),
            $this->field('rest_blocks', 'Mental-Rest Blocks / Day', 'number', ['min' => 0, 'max' => 10, 'step' => 1, 'default' => 1]),
            $this->field('alcohol_drinks_per_week', 'Alcohol Drinks / Week', 'number', ['min' => 0, 'max' => 40, 'step' => 1, 'default' => 4]),
            $this->field('sleep_hours', 'Average Sleep', 'number', ['min' => 3, 'max' => 12, 'step' => 0.1, 'default' => 6.5, 'unit' => 'hrs']),
            $this->field('wake_hour', 'Typical Wake Hour (24h)', 'number', ['min' => 4, 'max' => 12, 'step' => 0.5, 'default' => 7, 'unit' => 'h', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $decisions = $this->requireNumeric($inputs, 'daily_decisions');
        $trivialPct = $this->requireNumeric($inputs, 'trivial_pct') / 100;
        $routinePct = $this->requireNumeric($inputs, 'routine_pct') / 100;
        $rest = $this->requireNumeric($inputs, 'rest_blocks');
        $alcohol = $this->requireNumeric($inputs, 'alcohol_drinks_per_week');
        $sleep = $this->requireNumeric($inputs, 'sleep_hours');
        $wake = $this->toFloat($inputs, 'wake_hour', 7);

        $afterRoutine = $decisions * (1 - $routinePct);
        $meaningful = $afterRoutine * (1 - $trivialPct);
        $trivial = $afterRoutine * $trivialPct;

        // Rest recovery: each block restores ~8–12 decision units (glucose-willpower framing)
        $restRecovery = $rest * 10;
        $alcoholPenalty = min(25, $alcohol * 1.5); // weekly load spilling into daily quality
        $sleepPenalty = $sleep >= 7.5 ? 0 : ((7.5 - $sleep) / 3.5) * 30;

        $effectiveMeaningful = max(0, $meaningful + $restRecovery - $alcoholPenalty - $sleepPenalty);
        $qualityRatio = $meaningful > 0 ? $effectiveMeaningful / $meaningful : 0;

        // Decision quality crash: willpower depletes across the day; crash earlier with high load + low sleep
        $ceiling = 60; // Bargh & Vohs-style daily ceiling
        $hoursToCrash = max(3, min(14, $ceiling / max(4, $afterRoutine / 12)));
        if ($sleep < 6.5) {
            $hoursToCrash *= 0.85;
        }
        if ($alcohol >= 7) {
            $hoursToCrash *= 0.9;
        }
        $crashHour = $wake + $hoursToCrash;
        while ($crashHour >= 24) {
            $crashHour -= 24;
        }
        $crashLabel = sprintf('%02d:%02d', (int) floor($crashHour), (int) round(($crashHour - floor($crashHour)) * 60));

        return [
            'results' => [
                'decisions_after_routine' => $this->round($afterRoutine, 0),
                'meaningful_decisions' => $this->round($meaningful, 0),
                'effective_decisions_after_penalties' => $this->round($effectiveMeaningful, 0),
                'decision_quality_ratio_pct' => $this->round($qualityRatio * 100, 0),
                'quality_crash_clock_time' => $crashLabel,
                'top_fix' => $sleepPenalty >= $alcoholPenalty && $sleepPenalty >= 10
                    ? 'Sleep debt is taxing decision quality hardest'
                    : ($alcoholPenalty > 10
                        ? 'Alcohol load is eroding next-day willpower'
                        : ($trivial > $meaningful
                            ? 'Trivial decisions are crowding out meaningful ones — routinize or batch them'
                            : 'Add a midday rest block before your crash hour')),
            ],
            'breakdown' => [
                'trivial_after_routine' => $this->round($trivial, 0),
                'rest_recovery_units' => $this->round($restRecovery, 0),
                'alcohol_penalty_units' => $this->round($alcoholPenalty, 1),
                'sleep_penalty_units' => $this->round($sleepPenalty, 1),
                'anchors' => 'Bargh & Vohs / Vohs & Heatherton willpower · Walker sleep literature · ~60-decision daily ceiling',
                'formula' => 'Effective ≈ meaningful×(1−routine already removed) + rest − alcohol − sleep penalties; crash ≈ wake + f(load, sleep)',
            ],
            'units' => [
                'decisions_after_routine' => 'decisions',
                'meaningful_decisions' => 'decisions',
                'effective_decisions_after_penalties' => 'decisions',
                'decision_quality_ratio_pct' => '%',
                'quality_crash_clock_time' => '',
                'top_fix' => '',
            ],
        ];
    }
}
