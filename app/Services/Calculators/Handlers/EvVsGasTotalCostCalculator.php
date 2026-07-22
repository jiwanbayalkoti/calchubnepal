<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * EV vs Gas Total Cost Calculator
 * Annual operating savings, upfront premium recovery, and net savings over ownership.
 */
class EvVsGasTotalCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ev_vs_gas_total_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('ownership_years', 'Ownership Window', 'number', ['min' => 1, 'max' => 20, 'step' => 0.5, 'default' => 5, 'unit' => 'years']),
            $this->field('annual_miles', 'Annual Miles', 'number', ['min' => 1, 'max' => 100000, 'step' => 100, 'default' => 12000, 'unit' => 'mi/yr']),
            $this->field('ev_price', 'EV Purchase Price', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 42000, 'unit' => 'currency']),
            $this->field('gas_price_vehicle', 'Gas Car Purchase Price', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 32000, 'unit' => 'currency']),
            $this->field('ev_incentives', 'EV Rebates / Tax Credits', 'number', ['min' => 0, 'max' => 20000, 'step' => 1, 'default' => 7500, 'unit' => 'currency', 'required' => false]),
            $this->field('electricity_rate', 'Electricity Rate', 'number', ['min' => 0, 'max' => 2, 'step' => 0.001, 'default' => 0.14, 'unit' => 'currency/kWh']),
            $this->field('ev_kwh_per_100mi', 'EV Efficiency', 'number', ['min' => 10, 'max' => 80, 'step' => 0.1, 'default' => 30, 'unit' => 'kWh/100mi']),
            $this->field('gas_price', 'Gasoline Price / Gallon', 'number', ['min' => 0, 'max' => 20, 'step' => 0.01, 'default' => 3.50, 'unit' => 'currency']),
            $this->field('gas_mpg', 'Gas Car MPG', 'number', ['min' => 1, 'max' => 80, 'step' => 0.1, 'default' => 30, 'unit' => 'mpg']),
            $this->field('ev_annual_maint', 'EV Annual Maintenance', 'number', ['min' => 0, 'max' => 10000, 'step' => 1, 'default' => 400, 'unit' => 'currency', 'required' => false]),
            $this->field('gas_annual_maint', 'Gas Annual Maintenance', 'number', ['min' => 0, 'max' => 10000, 'step' => 1, 'default' => 900, 'unit' => 'currency', 'required' => false]),
            $this->field('ev_residual', 'EV Resale After Ownership', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 18000, 'unit' => 'currency', 'required' => false]),
            $this->field('gas_residual', 'Gas Resale After Ownership', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 14000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $years = max(1.0, $this->requireNumeric($inputs, 'ownership_years'));
        $miles = $this->requireNumeric($inputs, 'annual_miles');
        $evPrice = $this->requireNumeric($inputs, 'ev_price');
        $gasPriceVehicle = $this->requireNumeric($inputs, 'gas_price_vehicle');
        $incentives = $this->toFloat($inputs, 'ev_incentives', 0);
        $kwhRate = $this->requireNumeric($inputs, 'electricity_rate');
        $kwhPer100 = $this->requireNumeric($inputs, 'ev_kwh_per_100mi');
        $gasGallonPrice = $this->requireNumeric($inputs, 'gas_price');
        $mpg = $this->requireNumeric($inputs, 'gas_mpg');
        $evMaint = $this->toFloat($inputs, 'ev_annual_maint', 400);
        $gasMaint = $this->toFloat($inputs, 'gas_annual_maint', 900);
        $evResidual = $this->toFloat($inputs, 'ev_residual', 0);
        $gasResidual = $this->toFloat($inputs, 'gas_residual', 0);

        $evNetUpfront = $evPrice - $incentives;
        $upfrontPremium = $evNetUpfront - $gasPriceVehicle;

        $annualEvEnergy = ($miles / 100) * $kwhPer100 * $kwhRate;
        $annualGasFuel = $this->safeDivide($miles, $mpg) * $gasGallonPrice;
        $annualOpEv = $annualEvEnergy + $evMaint;
        $annualOpGas = $annualGasFuel + $gasMaint;
        $annualOpSavings = $annualOpGas - $annualOpEv;

        $monthsToRecover = $annualOpSavings > 0.01
            ? ($upfrontPremium <= 0 ? 0.0 : ($upfrontPremium / $annualOpSavings) * 12)
            : null;

        $evDepreciation = max(0, $evNetUpfront - $evResidual);
        $gasDepreciation = max(0, $gasPriceVehicle - $gasResidual);

        $evTotal = $evDepreciation + ($annualOpEv * $years);
        $gasTotal = $gasDepreciation + ($annualOpGas * $years);
        $netSavings = $gasTotal - $evTotal;

        $verdict = $netSavings > 100
            ? 'EV wins on total cost over this ownership window'
            : ($netSavings < -100
                ? 'Gas car wins on total cost over this ownership window'
                : 'Roughly even — pick based on driving style and incentives');

        return [
            'results' => [
                'upfront_ev_premium' => $this->round($upfrontPremium),
                'annual_operating_savings' => $this->round($annualOpSavings),
                'months_to_recover_premium' => $monthsToRecover === null
                    ? 'Never (EV operating cost not lower)'
                    : $this->round($monthsToRecover, 1),
                'ev_total_cost' => $this->round($evTotal),
                'gas_total_cost' => $this->round($gasTotal),
                'net_dollar_savings' => $this->round($netSavings),
                'verdict' => $verdict,
            ],
            'breakdown' => [
                'annual_ev_energy' => $this->round($annualEvEnergy),
                'annual_gas_fuel' => $this->round($annualGasFuel),
                'annual_ev_operating' => $this->round($annualOpEv),
                'annual_gas_operating' => $this->round($annualOpGas),
                'ev_net_upfront_after_incentives' => $this->round($evNetUpfront),
                'ev_depreciation' => $this->round($evDepreciation),
                'gas_depreciation' => $this->round($gasDepreciation),
                'formula' => 'Net savings = (gas depreciation + gas ops×years) − (EV depreciation + EV ops×years)',
            ],
            'units' => [
                'upfront_ev_premium' => 'currency',
                'annual_operating_savings' => 'currency/yr',
                'months_to_recover_premium' => 'months',
                'ev_total_cost' => 'currency',
                'gas_total_cost' => 'currency',
                'net_dollar_savings' => 'currency',
                'verdict' => '',
            ],
        ];
    }
}
