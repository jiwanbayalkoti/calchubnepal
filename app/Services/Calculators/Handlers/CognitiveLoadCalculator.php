<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Cognitive Load Calculator
 * 0–100 score vs Miller / Bargh-Vohs / Atlassian / Walker thresholds; top drag + reclaim.
 */
class CognitiveLoadCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'cognitive_load_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('active_projects', 'Active Projects', 'number', ['min' => 0, 'max' => 30, 'step' => 1, 'default' => 5]),
            $this->field('daily_decisions', 'Daily Conscious Decisions', 'number', ['min' => 0, 'max' => 300, 'step' => 1, 'default' => 70]),
            $this->field('open_todos', 'Open Todos', 'number', ['min' => 0, 'max' => 500, 'step' => 1, 'default' => 40]),
            $this->field('weekly_meetings', 'Weekly Meetings', 'number', ['min' => 0, 'max' => 80, 'step' => 1, 'default' => 12]),
            $this->field('daily_notifications', 'Daily Notifications', 'number', ['min' => 0, 'max' => 1000, 'step' => 1, 'default' => 80]),
            $this->field('sleep_hours_7day_avg', '7-Day Average Sleep', 'number', ['min' => 3, 'max' => 12, 'step' => 0.1, 'default' => 6.5, 'unit' => 'hrs']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $projects = $this->requireNumeric($inputs, 'active_projects');
        $decisions = $this->requireNumeric($inputs, 'daily_decisions');
        $todos = $this->requireNumeric($inputs, 'open_todos');
        $meetings = $this->requireNumeric($inputs, 'weekly_meetings');
        $notifs = $this->requireNumeric($inputs, 'daily_notifications');
        $sleep = $this->requireNumeric($inputs, 'sleep_hours_7day_avg');

        // Threshold anchors
        $projectLoad = min(25, ($projects / 7) * 25); // Miller ~7 working-memory slots
        $decisionLoad = min(25, ($decisions / 60) * 25); // Bargh & Vohs ~60-decision ceiling
        $todoLoad = min(15, ($todos / 30) * 15);
        $meetingLoad = min(15, ($meetings / 20) * 15);
        // Interruptions: each notif roughly costs recovery; Atlassian ~23 min recovery framing
        $interruptLoad = min(15, ($notifs / 50) * 15);
        $sleepPenalty = $sleep >= 7.5 ? 0 : min(20, ((7.5 - $sleep) / 3.5) * 20);

        $score = min(100, $projectLoad + $decisionLoad + $todoLoad + $meetingLoad + $interruptLoad + $sleepPenalty);

        $drags = [
            'Active projects overworking memory' => $projectLoad,
            'Daily decision volume' => $decisionLoad,
            'Open todo backlog' => $todoLoad,
            'Meeting load' => $meetingLoad,
            'Notification interruptions' => $interruptLoad,
            'Sleep deficit' => $sleepPenalty,
        ];
        arsort($drags);
        $topDrag = array_key_first($drags);

        $reclaim = match ($topDrag) {
            'Active projects overworking memory' => 'Cut to ≤3 active projects; park the rest on a someday list (Miller 7±2)',
            'Daily decision volume' => 'Template / batch trivial choices; cap discretionary decisions under ~60/day',
            'Open todo backlog' => 'Triage to a 10-item MIT list; archive or schedule the rest',
            'Meeting load' => 'Default to async; reclaim 2–3 recurring meetings this week',
            'Notification interruptions' => 'Batch checks 3×/day — each interrupt can cost ~23 min to fully recover',
            default => 'Protect a consistent 7.5–8.5 hr sleep window before optimizing anything else (Walker)',
        };

        $band = $score < 35 ? 'Manageable' : ($score < 60 ? 'Elevated' : ($score < 80 ? 'Overloaded' : 'Crisis-level load'));

        return [
            'results' => [
                'cognitive_load_score' => $this->round($score, 0),
                'load_band' => $band,
                'top_drag' => $topDrag,
                'highest_leverage_reclaim' => $reclaim,
            ],
            'breakdown' => [
                'project_component' => $this->round($projectLoad, 1),
                'decision_component' => $this->round($decisionLoad, 1),
                'todo_component' => $this->round($todoLoad, 1),
                'meeting_component' => $this->round($meetingLoad, 1),
                'notification_component' => $this->round($interruptLoad, 1),
                'sleep_penalty' => $this->round($sleepPenalty, 1),
                'anchors' => 'Miller 7-item WM · Bargh/Vohs ~60 decisions · Atlassian ~23-min interrupt recovery · Walker sleep',
            ],
            'units' => [
                'cognitive_load_score' => '/100',
                'load_band' => '',
                'top_drag' => '',
                'highest_leverage_reclaim' => '',
            ],
        ];
    }
}
