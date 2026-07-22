<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Best Day to Move Calculator
 * Optimal mid-month move date; winter rent discounts for tier-1 metros; savings vs worst month.
 */
class BestDayToMoveCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'best_day_to_move_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('city_tier', 'City Tier', 'select', [
                'options' => [
                    'tier1' => 'Tier-1 (NYC, SF, LA, Boston, DC, Chicago)',
                    'tier2' => 'Tier-2 major metro',
                    'tier3' => 'Tier-3 / smaller market',
                ],
                'default' => 'tier1',
            ]),
            $this->field('lease_end_month', 'Current Lease-End Month', 'select', [
                'options' => [
                    '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
                    '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
                    '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December',
                ],
                'default' => '8',
            ]),
            $this->field('flex_months', 'Flexibility Window (± months)', 'number', ['min' => 0, 'max' => 6, 'step' => 1, 'default' => 3, 'unit' => 'months']),
            $this->field('monthly_rent', 'Target / Current Monthly Rent', 'number', ['min' => 500, 'max' => 20000, 'step' => 50, 'default' => 3200, 'unit' => 'currency']),
            $this->field('unit_size', 'Unit Size', 'select', [
                'options' => ['studio' => 'Studio', '1br' => '1BR', '2br' => '2BR+', 'house' => 'House'],
                'default' => '1br',
            ]),
            $this->field('negotiation', 'Negotiation Propensity', 'select', [
                'options' => ['low' => 'Take list price', 'medium' => 'Ask once', 'high' => 'Strong negotiator'],
                'default' => 'medium',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $tier = $this->toString($inputs, 'city_tier', 'tier1');
        $leaseEnd = (int) $this->toString($inputs, 'lease_end_month', '8');
        $flex = (int) max(0, round($this->requireNumeric($inputs, 'flex_months')));
        $rent = $this->requireNumeric($inputs, 'monthly_rent');
        $size = $this->toString($inputs, 'unit_size', '1br');
        $nego = $this->toString($inputs, 'negotiation', 'medium');

        // Seasonal index: 1.0 = average; winter cheaper, summer peak
        $season = [
            1 => 0.90, 2 => 0.88, 3 => 0.94, 4 => 1.00,
            5 => 1.06, 6 => 1.10, 7 => 1.12, 8 => 1.11,
            9 => 1.05, 10 => 1.00, 11 => 0.94, 12 => 0.91,
        ];
        $tierDepth = match ($tier) {
            'tier1' => 1.0,   // full 6–12% winter swing
            'tier2' => 0.65,
            default => 0.35,
        };
        $sizeMult = match ($size) {
            'studio' => 0.95,
            '2br' => 1.05,
            'house' => 1.08,
            default => 1.0,
        };
        $negoDiscount = match ($nego) {
            'high' => 0.03,
            'medium' => 0.015,
            default => 0.0,
        };

        $candidates = [];
        for ($offset = -$flex; $offset <= $flex; $offset++) {
            $m = $leaseEnd + $offset;
            while ($m < 1) {
                $m += 12;
            }
            while ($m > 12) {
                $m -= 12;
            }
            $idx = 1 + (($season[$m] - 1) * $tierDepth);
            $effective = $rent * $idx * $sizeMult * (1 - $negoDiscount);
            $candidates[$m] = $effective;
        }

        asort($candidates);
        $bestMonth = (int) array_key_first($candidates);
        $bestRent = $candidates[$bestMonth];
        arsort($candidates);
        $worstMonth = (int) array_key_first($candidates);
        $worstRent = $candidates[$worstMonth];

        $annualSave = ($worstRent - $bestRent) * 12;
        $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];

        $winterNote = $tier === 'tier1'
            ? 'Tier-1 metros often see ~6–12% lower asking rents in mid-winter vs peak summer'
            : 'Smaller seasonal swing than tier-1; still prefer softer months when flexible';

        return [
            'results' => [
                'optimal_move_month' => $months[$bestMonth],
                'optimal_mid_month_date' => sprintf('%s 12–18 (mid-month listing lull)', $months[$bestMonth]),
                'estimated_rent_best' => $this->round($bestRent),
                'worst_month_in_window' => $months[$worstMonth],
                'estimated_rent_worst' => $this->round($worstRent),
                'annual_savings_vs_worst' => $this->round($annualSave),
                'seasonal_note' => $winterNote,
            ],
            'breakdown' => [
                'negotiation_discount_applied_pct' => $this->round($negoDiscount * 100, 1),
                'formula' => 'Score months in flex window by seasonal index × tier depth; mid-month dates reduce competition vs month-start rush',
            ],
            'units' => [
                'optimal_move_month' => '',
                'optimal_mid_month_date' => '',
                'estimated_rent_best' => 'currency/mo',
                'worst_month_in_window' => '',
                'estimated_rent_worst' => 'currency/mo',
                'annual_savings_vs_worst' => 'currency/yr',
                'seasonal_note' => '',
            ],
        ];
    }
}
