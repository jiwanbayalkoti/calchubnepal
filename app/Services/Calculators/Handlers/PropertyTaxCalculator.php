<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Property Tax Calculator
 * Effective rate from US Census-style state medians, annual bill, escrow, vs national avg.
 */
class PropertyTaxCalculator extends AbstractCalculatorHandler
{
    /** @var array<string, float> effective rate % of home value (illustrative Census medians) */
    protected const STATE_RATES = [
        'AL' => 0.40, 'AK' => 1.04, 'AZ' => 0.62, 'AR' => 0.61, 'CA' => 0.75,
        'CO' => 0.51, 'CT' => 1.79, 'DE' => 0.57, 'FL' => 0.89, 'GA' => 0.91,
        'HI' => 0.28, 'ID' => 0.67, 'IL' => 2.08, 'IN' => 0.84, 'IA' => 1.52,
        'KS' => 1.34, 'KY' => 0.83, 'LA' => 0.55, 'ME' => 1.24, 'MD' => 1.07,
        'MA' => 1.14, 'MI' => 1.35, 'MN' => 1.11, 'MS' => 0.77, 'MO' => 0.97,
        'MT' => 0.83, 'NE' => 1.63, 'NV' => 0.59, 'NH' => 1.93, 'NJ' => 2.23,
        'NM' => 0.74, 'NY' => 1.40, 'NC' => 0.82, 'ND' => 0.98, 'OH' => 1.52,
        'OK' => 0.87, 'OR' => 0.93, 'PA' => 1.50, 'RI' => 1.45, 'SC' => 0.55,
        'SD' => 1.22, 'TN' => 0.66, 'TX' => 1.68, 'UT' => 0.58, 'VT' => 1.76,
        'VA' => 0.82, 'WA' => 0.94, 'WV' => 0.57, 'WI' => 1.61, 'WY' => 0.60,
        'DC' => 0.56,
    ];

    protected const NATIONAL_AVG = 1.07;

    public function key(): string
    {
        return 'property_tax_calculator';
    }

    public function inputSchema(): array
    {
        $states = [];
        foreach (array_keys(self::STATE_RATES) as $code) {
            $states[$code] = $code;
        }

        return [
            $this->field('home_value', 'Home Market / Assessed Value', 'number', ['min' => 10000, 'max' => 20000000, 'step' => 1000, 'default' => 450000, 'unit' => 'currency']),
            $this->field('state', 'State', 'select', [
                'options' => $states,
                'default' => 'TX',
            ]),
            $this->field('county_rate_override', 'County Effective Rate Override', 'number', ['min' => 0, 'max' => 5, 'step' => 0.01, 'default' => 0, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'home_value');
        $state = strtoupper($this->toString($inputs, 'state', 'TX'));
        $override = $this->toFloat($inputs, 'county_rate_override', 0);
        $stateRate = self::STATE_RATES[$state] ?? self::NATIONAL_AVG;
        $rate = $override > 0 ? $override : $stateRate;

        $annual = $value * ($rate / 100);
        $monthly = $annual / 12;
        $vsNational = $rate - self::NATIONAL_AVG;
        $vsState = $override > 0 ? $rate - $stateRate : 0.0;

        return [
            'results' => [
                'effective_property_tax_rate_pct' => $this->round($rate, 2),
                'annual_property_tax' => $this->round($annual),
                'monthly_escrow' => $this->round($monthly),
                'vs_national_avg_pp' => $this->round($vsNational, 2),
                'state_median_rate_pct' => $this->round($stateRate, 2),
                'national_avg_rate_pct' => self::NATIONAL_AVG,
                'comparison' => $rate > self::NATIONAL_AVG
                    ? 'Above US average effective rate'
                    : 'At or below US average effective rate',
            ],
            'breakdown' => [
                'county_override_used' => $override > 0 ? 'Yes' : 'No',
                'vs_state_median_pp' => $this->round($vsState, 2),
                'note' => 'Rates approximate US Census effective property-tax medians; local millage varies — use county override for hyperlocal accuracy.',
                'formula' => 'Annual tax ≈ home value × effective rate%',
            ],
            'units' => [
                'effective_property_tax_rate_pct' => '%',
                'annual_property_tax' => 'currency',
                'monthly_escrow' => 'currency',
                'vs_national_avg_pp' => 'pp',
                'state_median_rate_pct' => '%',
                'national_avg_rate_pct' => '%',
                'comparison' => '',
            ],
        ];
    }
}
