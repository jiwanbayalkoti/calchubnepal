<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Solar Panel Cost Calculator
 * Quick size + installed cost + payback + 25-yr net savings.
 * Federal §25D credit defaults to $0 for 2026 installs.
 */
class SolarPanelCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'solar_panel_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_bill', 'Monthly Electric Bill', 'number', ['min' => 1, 'max' => 5000, 'step' => 1, 'default' => 150, 'unit' => 'currency']),
            $this->field('electric_rate', 'Electric Rate', 'number', ['min' => 0.01, 'max' => 1, 'step' => 0.001, 'default' => 0.15, 'unit' => 'currency/kWh']),
            $this->field('sun_hours', 'Peak Sun Hours (by state/region)', 'number', ['min' => 2.5, 'max' => 6.5, 'step' => 0.1, 'default' => 4.5, 'unit' => 'hrs/day']),
            $this->field('roof_orientation', 'Roof Orientation', 'select', [
                'options' => [
                    'south' => 'South (best)',
                    'southeast_southwest' => 'SE / SW',
                    'east_west' => 'East / West',
                    'north' => 'North (poor)',
                ],
                'default' => 'south',
            ]),
            $this->field('cost_per_watt', 'Installed Cost / Watt', 'number', ['min' => 1.5, 'max' => 6, 'step' => 0.05, 'default' => 3.0, 'unit' => 'currency/W', 'required' => false]),
            $this->field('federal_credit_pct', 'Federal Credit % (0 for 2026+)', 'number', ['min' => 0, 'max' => 30, 'step' => 1, 'default' => 0, 'unit' => '%', 'required' => false]),
            $this->field('state_incentive', 'State / Utility Incentive', 'number', ['min' => 0, 'max' => 30000, 'step' => 50, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('price_escalation', 'Rate Escalation', 'number', ['min' => 0, 'max' => 8, 'step' => 0.1, 'default' => 2.5, 'unit' => '%/yr', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $bill = $this->requireNumeric($inputs, 'monthly_bill');
        $rate = $this->requireNumeric($inputs, 'electric_rate');
        $sun = $this->requireNumeric($inputs, 'sun_hours');
        $orient = $this->toString($inputs, 'roof_orientation', 'south');
        $cpw = $this->toFloat($inputs, 'cost_per_watt', 3.0);
        $fed = $this->toFloat($inputs, 'federal_credit_pct', 0) / 100;
        $state = $this->toFloat($inputs, 'state_incentive', 0);
        $esc = $this->toFloat($inputs, 'price_escalation', 2.5) / 100;

        $orientFactor = match ($orient) {
            'southeast_southwest' => 0.95,
            'east_west' => 0.85,
            'north' => 0.65,
            default => 1.0,
        };

        $annualKwh = $this->safeDivide($bill, $rate) * 12;
        $systemKw = $this->safeDivide($annualKwh, $sun * 365 * 0.82 * $orientFactor);
        $systemKw = max(1.0, round($systemKw * 2) / 2);

        $gross = $systemKw * 1000 * $cpw;
        $net = max(0, $gross * (1 - $fed) - $state);
        $year1Prod = $systemKw * $sun * 365 * 0.82 * $orientFactor;

        $cum = -$net;
        $payback = null;
        $savings25 = 0.0;
        for ($y = 1; $y <= 25; $y++) {
            $prod = $year1Prod * ((1 - 0.005) ** ($y - 1));
            $value = $prod * $rate * ((1 + $esc) ** ($y - 1));
            $savings25 += $value;
            $cum += $value;
            if ($payback === null && $cum >= 0) {
                $payback = $y;
            }
        }

        return [
            'results' => [
                'system_size_kw' => $this->round($systemKw, 1),
                'total_installed_cost' => $this->round($gross),
                'net_cost_after_incentives' => $this->round($net),
                'payback_years' => $payback === null ? 'Beyond 25 years' : $payback,
                'net_savings_25yr' => $this->round($savings25 - $net),
            ],
            'breakdown' => [
                'year1_kwh' => $this->round($year1Prod),
                'orientation_factor' => $orientFactor,
                'federal_credit_note' => 'IRS §25D expired Dec 31 2025 — federal credit $0 for 2026 installs unless you override.',
                'see_also' => 'For full IRR + battery + NEM modeling, use the Solar ROI Calculator.',
            ],
            'units' => [
                'system_size_kw' => 'kW',
                'total_installed_cost' => 'currency',
                'net_cost_after_incentives' => 'currency',
                'payback_years' => 'years',
                'net_savings_25yr' => 'currency',
            ],
        ];
    }
}
