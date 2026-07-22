<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Electricity Bill Optimizer (TOU)
 * Manual load-shift savings + battery arbitrage; battery payback at your TOU spread.
 */
class ElectricityBillOptimizerTouCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'electricity_bill_optimizer_tou_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_kwh', 'Monthly Usage', 'number', ['min' => 50, 'max' => 5000, 'step' => 10, 'default' => 750, 'unit' => 'kWh']),
            $this->field('peak_rate', 'Peak TOU Rate', 'number', ['min' => 0.05, 'max' => 1.5, 'step' => 0.01, 'default' => 0.42, 'unit' => 'currency/kWh']),
            $this->field('offpeak_rate', 'Off-Peak TOU Rate', 'number', ['min' => 0.02, 'max' => 1, 'step' => 0.01, 'default' => 0.18, 'unit' => 'currency/kWh']),
            $this->field('peak_share_pct', 'Share of kWh on Peak (today)', 'number', ['min' => 5, 'max' => 80, 'step' => 1, 'default' => 35, 'unit' => '%']),
            $this->field('ev_charging', 'EV Charging Timing', 'select', [
                'options' => [
                    'peak' => 'Mostly peak',
                    'mixed' => 'Mixed',
                    'offpeak' => 'Mostly off-peak',
                    'none' => 'No EV',
                ],
                'default' => 'mixed',
            ]),
            $this->field('flexibility', 'Appliance Flexibility', 'select', [
                'options' => [
                    'low' => 'Low — little can shift',
                    'medium' => 'Medium — dishwasher/dryer/EV',
                    'high' => 'High — thermostat + water + EV',
                ],
                'default' => 'medium',
            ]),
            $this->field('shift_hours_per_week', 'Willingness to Shift (hrs/wk)', 'number', ['min' => 0, 'max' => 40, 'step' => 1, 'default' => 8, 'unit' => 'hrs', 'required' => false]),
            $this->field('include_battery', 'Battery Arbitrage Option', 'select', [
                'options' => ['no' => 'No battery', 'yes' => 'Model battery'],
                'default' => 'yes',
            ]),
            $this->field('battery_cost', 'Battery Installed Cost', 'number', ['min' => 0, 'max' => 30000, 'step' => 100, 'default' => 12000, 'unit' => 'currency', 'required' => false]),
            $this->field('battery_kwh', 'Usable Battery kWh', 'number', ['min' => 3, 'max' => 40, 'step' => 0.5, 'default' => 13.5, 'unit' => 'kWh', 'required' => false]),
            $this->field('battery_credit_pct', 'Battery Credit % (0 for 2026+ federal)', 'number', ['min' => 0, 'max' => 30, 'step' => 1, 'default' => 0, 'unit' => '%', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $kwh = $this->requireNumeric($inputs, 'monthly_kwh');
        $peak = $this->requireNumeric($inputs, 'peak_rate');
        $off = $this->requireNumeric($inputs, 'offpeak_rate');
        $peakShare = $this->requireNumeric($inputs, 'peak_share_pct') / 100;
        $ev = $this->toString($inputs, 'ev_charging', 'mixed');
        $flex = $this->toString($inputs, 'flexibility', 'medium');
        $shiftHrs = $this->toFloat($inputs, 'shift_hours_per_week', 8);
        $useBatt = $this->toString($inputs, 'include_battery', 'yes') === 'yes';
        $battCost = $this->toFloat($inputs, 'battery_cost', 12000);
        $battKwh = $this->toFloat($inputs, 'battery_kwh', 13.5);
        $credit = $this->toFloat($inputs, 'battery_credit_pct', 0) / 100;

        $spread = max(0, $peak - $off);
        $billNow = $kwh * (($peakShare * $peak) + ((1 - $peakShare) * $off));

        $flexFrac = match ($flex) {
            'low' => 0.08,
            'high' => 0.35,
            default => 0.2,
        };
        $evBoost = match ($ev) {
            'peak' => 0.08,
            'offpeak' => -0.03,
            'none' => 0.0,
            default => 0.04,
        };
        $willingness = min(1.0, $shiftHrs / 20);
        $shiftableShare = min($peakShare, ($flexFrac + $evBoost) * $willingness);

        $manualMonthlySavings = $kwh * $shiftableShare * $spread;
        $manualAnnual = $manualMonthlySavings * 12;

        $battDailyCycles = min(1.0, $this->safeDivide($kwh / 30 * $peakShare, max(0.1, $battKwh)));
        $battMonthlyArbitrage = $useBatt ? $battKwh * $battDailyCycles * 30 * $spread * 0.9 : 0; // 90% RTE
        $battAnnual = $battMonthlyArbitrage * 12;
        $battNet = $battCost * (1 - $credit);
        $battPayback = ($useBatt && $battAnnual > 0) ? $battNet / $battAnnual : null;

        $recommendBattery = $useBatt && $battPayback !== null && $battPayback <= 12 && $spread >= 0.15 && $billNow >= 120;

        return [
            'results' => [
                'current_monthly_bill_est' => $this->round($billNow),
                'tou_spread' => $this->round($spread, 3),
                'manual_shift_monthly_savings' => $this->round($manualMonthlySavings),
                'manual_shift_annual_savings' => $this->round($manualAnnual),
                'battery_arbitrage_monthly' => $this->round($battMonthlyArbitrage),
                'battery_arbitrage_annual' => $this->round($battAnnual),
                'battery_simple_payback_years' => $battPayback === null ? 'n/a' : $this->round($battPayback, 1),
                'battery_recommendation' => $recommendBattery
                    ? 'Battery likely pays back at your TOU spread + bill size'
                    : 'Prioritize manual shifting first — battery payback looks long or spread is thin',
            ],
            'breakdown' => [
                'shiftable_peak_share' => $this->round($shiftableShare * 100, 1),
                'battery_net_cost' => $this->round($battNet),
                'formula' => 'Manual savings ≈ kWh × shiftable_peak_share × (peak−offpeak); battery ≈ kWh_cycled × spread × RTE',
            ],
            'units' => [
                'current_monthly_bill_est' => 'currency',
                'tou_spread' => 'currency/kWh',
                'manual_shift_monthly_savings' => 'currency',
                'manual_shift_annual_savings' => 'currency',
                'battery_arbitrage_monthly' => 'currency',
                'battery_arbitrage_annual' => 'currency',
                'battery_simple_payback_years' => 'years',
                'battery_recommendation' => '',
            ],
        ];
    }
}
