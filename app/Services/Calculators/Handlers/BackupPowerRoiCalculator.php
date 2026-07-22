<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Backup Power ROI — Powerwall vs Generator
 * 10-year NPV per option with optional IRA-era credit, comfort value, WFH outage cost.
 */
class BackupPowerRoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'backup_power_roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('outage_hours_per_year', 'Expected Outage Hours / Year', 'number', ['min' => 0, 'max' => 500, 'step' => 1, 'default' => 24, 'unit' => 'hrs']),
            $this->field('critical_load_kw', 'Critical Load', 'number', ['min' => 0.5, 'max' => 20, 'step' => 0.1, 'default' => 3.0, 'unit' => 'kW']),
            $this->field('wfh_hourly_value', 'WFH / Productivity Value of Power', 'number', ['min' => 0, 'max' => 500, 'step' => 1, 'default' => 40, 'unit' => 'currency/hr']),
            $this->field('comfort_value_per_outage_hr', 'Comfort / Safety Value-Add', 'number', ['min' => 0, 'max' => 200, 'step' => 1, 'default' => 15, 'unit' => 'currency/hr', 'required' => false]),
            $this->field('battery_install_cost', 'Battery (e.g. Powerwall) Install Cost', 'number', ['min' => 1000, 'max' => 40000, 'step' => 100, 'default' => 14500, 'unit' => 'currency']),
            $this->field('generator_install_cost', 'Standby Generator Install Cost', 'number', ['min' => 1000, 'max' => 40000, 'step' => 100, 'default' => 9000, 'unit' => 'currency']),
            $this->field('generator_fuel_per_hr', 'Generator Fuel Cost / Hour', 'number', ['min' => 0, 'max' => 50, 'step' => 0.5, 'default' => 4.5, 'unit' => 'currency/hr']),
            $this->field('battery_credit_pct', 'Battery Federal/State Credit %', 'number', ['min' => 0, 'max' => 30, 'step' => 1, 'default' => 0, 'unit' => '%', 'required' => false]),
            $this->field('discount_rate', 'Discount Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('battery_kwh', 'Battery Usable Capacity', 'number', ['min' => 5, 'max' => 40, 'step' => 0.5, 'default' => 13.5, 'unit' => 'kWh', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $outageHrs = $this->requireNumeric($inputs, 'outage_hours_per_year');
        $loadKw = $this->requireNumeric($inputs, 'critical_load_kw');
        $wfh = $this->requireNumeric($inputs, 'wfh_hourly_value');
        $comfort = $this->toFloat($inputs, 'comfort_value_per_outage_hr', 15);
        $battCost = $this->requireNumeric($inputs, 'battery_install_cost');
        $genCost = $this->requireNumeric($inputs, 'generator_install_cost');
        $fuelHr = $this->requireNumeric($inputs, 'generator_fuel_per_hr');
        $credit = $this->toFloat($inputs, 'battery_credit_pct', 0) / 100;
        $r = $this->toFloat($inputs, 'discount_rate', 5) / 100;
        $battKwh = $this->toFloat($inputs, 'battery_kwh', 13.5);

        $annualOutageValue = $outageHrs * ($wfh + $comfort);
        $battNet = $battCost * (1 - $credit);
        $battRuntimeHrs = $this->safeDivide($battKwh, $loadKw);
        $battCoverage = min(1.0, $this->safeDivide($battRuntimeHrs, max(1, $outageHrs / max(1, 4)))); // rough multi-event
        $battAnnualBenefit = $annualOutageValue * min(1.0, $battRuntimeHrs / max(0.5, $outageHrs / 6));

        $genAnnualFuel = $outageHrs * $fuelHr;
        $genAnnualBenefit = $annualOutageValue; // full coverage assumed
        $genAnnualNet = $genAnnualBenefit - $genAnnualFuel - 200; // ~$200 maint

        $battNpv = -$battNet;
        $genNpv = -$genCost;
        for ($y = 1; $y <= 10; $y++) {
            $battNpv += $battAnnualBenefit / ((1 + $r) ** $y);
            $genNpv += $genAnnualNet / ((1 + $r) ** $y);
        }

        $winner = $battNpv >= $genNpv ? 'Battery (Powerwall-class)' : 'Standby Generator';
        $recommended = $battRuntimeHrs >= 4
            ? sprintf('Battery ~%.1f kWh covers ~%.1f hrs of %.1f kW critical load', $battKwh, $battRuntimeHrs, $loadKw)
            : sprintf('Consider %.0f kWh+ battery or generator — current battery covers only %.1f hrs', $loadKw * 8, $battRuntimeHrs);

        return [
            'results' => [
                'battery_10yr_npv' => $this->round($battNpv),
                'generator_10yr_npv' => $this->round($genNpv),
                'recommended_option' => $winner,
                'battery_runtime_hours' => $this->round($battRuntimeHrs, 1),
                'annual_outage_value_monetized' => $this->round($annualOutageValue),
                'configuration_note' => $recommended,
            ],
            'breakdown' => [
                'battery_net_cost' => $this->round($battNet),
                'generator_annual_fuel' => $this->round($genAnnualFuel),
                'wfh_component' => $this->round($outageHrs * $wfh),
                'comfort_component' => $this->round($outageHrs * $comfort),
                'note' => 'IRA-era 30% battery credit often $0 for new 2026 claims — set credit % if you still qualify.',
            ],
            'units' => [
                'battery_10yr_npv' => 'currency',
                'generator_10yr_npv' => 'currency',
                'recommended_option' => '',
                'battery_runtime_hours' => 'hrs',
                'annual_outage_value_monetized' => 'currency/yr',
                'configuration_note' => '',
            ],
        ];
    }
}
