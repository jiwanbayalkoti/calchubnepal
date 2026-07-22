<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Probability of Success Calculator
 * Base rates by project type, adjusted for experience / team / runway / timeline.
 */
class ProbabilityOfSuccessCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'probability_of_success_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('project_type', 'Project Type', 'select', [
                'options' => [
                    'saas_cold' => 'Cold-start SaaS (~4%)',
                    'restaurant' => 'Restaurant (~30%)',
                    'book' => 'Book / creative publish (~8%)',
                    'agency' => 'Agency / services (~22%)',
                    'ecommerce' => 'E-commerce (~10%)',
                    'mobile_app' => 'Mobile app (~5%)',
                    'course' => 'Online course (~12%)',
                    'nonprofit' => 'Nonprofit launch (~18%)',
                ],
                'default' => 'saas_cold',
            ]),
            $this->field('prior_experience', 'Prior Experience', 'select', [
                'options' => [
                    'none' => 'None in this domain',
                    'adjacent' => 'Adjacent experience',
                    'one_win' => 'One prior win',
                    'repeat' => 'Repeat founder / pro',
                ],
                'default' => 'adjacent',
            ]),
            $this->field('team_size', 'Team Size', 'number', ['min' => 1, 'max' => 50, 'step' => 1, 'default' => 2]),
            $this->field('runway_months', 'Runway (months)', 'number', ['min' => 0, 'max' => 60, 'step' => 1, 'default' => 12, 'unit' => 'months']),
            $this->field('timeline_realism', 'Timeline Realism', 'select', [
                'options' => [
                    'optimistic' => 'Optimistic / aggressive',
                    'realistic' => 'Realistic',
                    'conservative' => 'Conservative buffer',
                ],
                'default' => 'realistic',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $type = $this->toString($inputs, 'project_type', 'saas_cold');
        $exp = $this->toString($inputs, 'prior_experience', 'adjacent');
        $team = max(1, $this->requireNumeric($inputs, 'team_size'));
        $runway = $this->requireNumeric($inputs, 'runway_months');
        $timeline = $this->toString($inputs, 'timeline_realism', 'realistic');

        $base = match ($type) {
            'restaurant' => 0.30,
            'book' => 0.08,
            'agency' => 0.22,
            'ecommerce' => 0.10,
            'mobile_app' => 0.05,
            'course' => 0.12,
            'nonprofit' => 0.18,
            default => 0.04,
        };

        $expMult = match ($exp) {
            'none' => 0.7,
            'one_win' => 1.35,
            'repeat' => 1.7,
            default => 1.0,
        };
        $teamMult = match (true) {
            $team >= 5 => 1.25,
            $team >= 3 => 1.15,
            $team == 1 => 0.85,
            default => 1.0,
        };
        $runwayMult = match (true) {
            $runway >= 18 => 1.3,
            $runway >= 12 => 1.15,
            $runway >= 6 => 1.0,
            $runway >= 3 => 0.75,
            default => 0.5,
        };
        $timeMult = match ($timeline) {
            'optimistic' => 0.8,
            'conservative' => 1.15,
            default => 1.0,
        };

        $adjusted = min(0.85, max(0.01, $base * $expMult * $teamMult * $runwayMult * $timeMult));

        $risks = [
            'runway' => ['label' => 'Insufficient runway', 'score' => $runway < 6 ? 3 : ($runway < 12 ? 2 : 0)],
            'experience' => ['label' => 'Thin domain experience', 'score' => $exp === 'none' ? 3 : ($exp === 'adjacent' ? 1 : 0)],
            'timeline' => ['label' => 'Unrealistic timeline', 'score' => $timeline === 'optimistic' ? 3 : 0],
            'team' => ['label' => 'Solo / understaffed execution', 'score' => $team == 1 ? 2 : 0],
            'base_rate' => ['label' => 'Harsh category base rate', 'score' => $base <= 0.05 ? 2 : ($base <= 0.10 ? 1 : 0)],
        ];
        uasort($risks, fn ($a, $b) => $b['score'] <=> $a['score']);
        $topRisk = array_key_first($risks);
        $biggestRisk = $risks[$topRisk]['score'] > 0
            ? $risks[$topRisk]['label']
            : 'No single dominant drag — execution quality is the remaining variance';

        return [
            'results' => [
                'category_base_rate_pct' => $this->round($base * 100, 1),
                'adjusted_probability_pct' => $this->round($adjusted * 100, 1),
                'lift_vs_base_pp' => $this->round(($adjusted - $base) * 100, 1),
                'biggest_risk' => $biggestRisk,
                'honest_read' => $adjusted < 0.1
                    ? 'Long-shot — plan for learning value, not certainty'
                    : ($adjusted < 0.25
                        ? 'Uphill but plausible with disciplined execution'
                        : 'Above-average odds for this category — still non-guaranteed'),
            ],
            'breakdown' => [
                'experience_multiplier' => $expMult,
                'team_multiplier' => $teamMult,
                'runway_multiplier' => $runwayMult,
                'timeline_multiplier' => $timeMult,
                'formula' => 'Adjusted p = base_rate × experience × team × runway × timeline (capped)',
            ],
            'units' => [
                'category_base_rate_pct' => '%',
                'adjusted_probability_pct' => '%',
                'lift_vs_base_pp' => 'pp',
                'biggest_risk' => '',
                'honest_read' => '',
            ],
        ];
    }
}
