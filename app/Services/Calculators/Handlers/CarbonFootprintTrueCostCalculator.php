<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Carbon Footprint True Cost Calculator
 * Aggregates 7 emission buckets, compares to US median, costs offsets, ranks levers.
 */
class CarbonFootprintTrueCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'carbon_footprint_true_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('flight_tier', 'Flight Intensity', 'select', [
                'options' => [
                    'none' => 'None',
                    'low' => '1–2 short trips / yr',
                    'medium' => 'A few domestic + 1 long-haul',
                    'high' => 'Frequent flyer / multiple long-hauls',
                ],
                'default' => 'medium',
            ]),
            $this->field('annual_miles', 'Annual Miles Driven', 'number', ['min' => 0, 'max' => 80000, 'step' => 500, 'default' => 10000, 'unit' => 'mi']),
            $this->field('diet', 'Diet', 'select', [
                'options' => [
                    'vegan' => 'Vegan',
                    'vegetarian' => 'Vegetarian',
                    'pescatarian' => 'Pescatarian',
                    'omnivore' => 'Average omnivore',
                    'high_meat' => 'High meat',
                ],
                'default' => 'omnivore',
            ]),
            $this->field('home_sqft', 'Home Size', 'number', ['min' => 200, 'max' => 10000, 'step' => 50, 'default' => 1800, 'unit' => 'sq.ft']),
            $this->field('electricity_source', 'Electricity Source', 'select', [
                'options' => [
                    'coal_heavy' => 'Coal-heavy grid',
                    'average' => 'Average US grid',
                    'clean' => 'Clean / mostly renewable',
                    'renewable' => '100% renewable / onsite solar',
                ],
                'default' => 'average',
            ]),
            $this->field('electronics_replacement', 'Electronics Replacement Pace', 'select', [
                'options' => ['slow' => 'Keep 5+ years', 'average' => 'Average', 'fast' => 'Upgrade often'],
                'default' => 'average',
            ]),
            $this->field('shopping_intensity', 'Shopping / Goods Intensity', 'select', [
                'options' => ['low' => 'Low', 'average' => 'Average', 'high' => 'High'],
                'default' => 'average',
            ]),
            $this->field('offset_price', 'Offset Price / Tonne', 'number', ['min' => 5, 'max' => 100, 'step' => 1, 'default' => 25, 'unit' => 'currency/t', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $flight = match ($this->toString($inputs, 'flight_tier', 'medium')) {
            'none' => 0.0,
            'low' => 0.6,
            'high' => 6.5,
            default => 2.4,
        };
        $miles = $this->requireNumeric($inputs, 'annual_miles');
        $drive = $miles * 0.000404; // ~404 g/mi ICE blend ≈ t

        $diet = match ($this->toString($inputs, 'diet', 'omnivore')) {
            'vegan' => 1.5,
            'vegetarian' => 1.7,
            'pescatarian' => 2.0,
            'high_meat' => 3.3,
            default => 2.5,
        };

        $sqft = $this->requireNumeric($inputs, 'home_sqft');
        $homeBase = $sqft * 0.0011; // rough residential energy CO2e
        $elecMult = match ($this->toString($inputs, 'electricity_source', 'average')) {
            'coal_heavy' => 1.35,
            'clean' => 0.55,
            'renewable' => 0.15,
            default => 1.0,
        };
        $home = $homeBase * $elecMult;

        $electronics = match ($this->toString($inputs, 'electronics_replacement', 'average')) {
            'slow' => 0.2,
            'fast' => 0.7,
            default => 0.4,
        };
        $shopping = match ($this->toString($inputs, 'shopping_intensity', 'average')) {
            'low' => 0.8,
            'high' => 2.2,
            default => 1.4,
        };

        // Misc household / services residual
        $other = 1.2;

        $buckets = [
            'flights' => $flight,
            'driving' => $drive,
            'diet' => $diet,
            'home_energy' => $home,
            'electronics' => $electronics,
            'shopping_goods' => $shopping,
            'other_services' => $other,
        ];
        $total = array_sum($buckets);
        $usMedian = 16.0;
        $offsetPrice = $this->toFloat($inputs, 'offset_price', 25);
        $offsetCost = $total * $offsetPrice;

        // Reduction levers (t saved, rough cost framing)
        $levers = [
            ['lever' => 'Cut long-haul flights / fly economy less often', 't_saved' => min($flight, max(0, $flight - 0.6)), 'cost_frame' => 'Often $0/t or saves money'],
            ['lever' => 'Shift diet toward lower-meat / plant-rich', 't_saved' => max(0, $diet - 1.7), 'cost_frame' => 'Usually $0/t or saves grocery spend'],
            ['lever' => 'Cleaner electricity / heat pump + weatherization', 't_saved' => max(0, $home - ($homeBase * 0.55)), 'cost_frame' => 'Capex now, OpEx savings later'],
            ['lever' => 'Drive less / EV / carpool', 't_saved' => max(0, $drive * 0.5), 'cost_frame' => 'Mixed — can save fuel cost'],
            ['lever' => 'Buy less / keep electronics longer', 't_saved' => max(0, ($electronics + $shopping) * 0.35), 'cost_frame' => '$0/t — saves money'],
        ];
        usort($levers, fn ($a, $b) => $b['t_saved'] <=> $a['t_saved']);
        $top = array_slice($levers, 0, 3);

        return [
            'results' => [
                'annual_co2e_tonnes' => $this->round($total, 2),
                'vs_us_median_16t' => $this->round($total - $usMedian, 2),
                'offset_cost_annual' => $this->round($offsetCost),
                'top_lever_1' => $top[0]['lever'].' (~'.$this->round($top[0]['t_saved'], 2).' t) — '.$top[0]['cost_frame'],
                'top_lever_2' => $top[1]['lever'].' (~'.$this->round($top[1]['t_saved'], 2).' t) — '.$top[1]['cost_frame'],
                'top_lever_3' => $top[2]['lever'].' (~'.$this->round($top[2]['t_saved'], 2).' t) — '.$top[2]['cost_frame'],
            ],
            'breakdown' => array_merge(
                array_map(fn ($v) => $this->round($v, 2), $buckets),
                ['us_median_t' => $usMedian, 'formula' => 'Sum of 7 EPA/Drawdown-style buckets; levers ranked by absolute t saved']
            ),
            'units' => [
                'annual_co2e_tonnes' => 't CO₂e/yr',
                'vs_us_median_16t' => 't CO₂e',
                'offset_cost_annual' => 'currency',
                'top_lever_1' => '',
                'top_lever_2' => '',
                'top_lever_3' => '',
            ],
        ];
    }
}
