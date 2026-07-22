<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * EV vs ICE TCO Calculator
 * 5-year total cost of ownership with fuel/maint/insurance/resale gaps and
 * electricity-rate flip point. Federal §30D $7,500 credit expired Sep 30 2025 — default $0.
 */
class EvVsIceTcoCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'ev_vs_ice_tco_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('ev_price', 'EV Price', 'number', ['min' => 0, 'max' => 200000, 'step' => 100, 'default' => 45000, 'unit' => 'currency']),
            $this->field('ice_price', 'ICE Price', 'number', ['min' => 0, 'max' => 200000, 'step' => 100, 'default' => 32000, 'unit' => 'currency']),
            $this->field('annual_miles', 'Annual Miles', 'number', ['min' => 1000, 'max' => 80000, 'step' => 500, 'default' => 12000, 'unit' => 'mi']),
            $this->field('electricity_rate', 'Electricity Rate', 'number', ['min' => 0.05, 'max' => 0.8, 'step' => 0.01, 'default' => 0.16, 'unit' => 'currency/kWh']),
            $this->field('ev_kwh_per_100mi', 'EV kWh / 100 mi', 'number', ['min' => 15, 'max' => 60, 'step' => 0.5, 'default' => 30, 'unit' => 'kWh']),
            $this->field('gas_price', 'Gas Price / Gallon', 'number', ['min' => 1, 'max' => 10, 'step' => 0.05, 'default' => 3.50, 'unit' => 'currency']),
            $this->field('gas_growth', 'Gas Price Trajectory', 'number', ['min' => -5, 'max' => 15, 'step' => 0.5, 'default' => 3, 'unit' => '%/yr', 'required' => false]),
            $this->field('ice_mpg', 'ICE MPG', 'number', ['min' => 10, 'max' => 60, 'step' => 0.5, 'default' => 28, 'unit' => 'mpg']),
            $this->field('ev_maint_annual', 'EV Maintenance / Year', 'number', ['min' => 0, 'max' => 5000, 'step' => 50, 'default' => 450, 'unit' => 'currency', 'required' => false]),
            $this->field('ice_maint_annual', 'ICE Maintenance / Year', 'number', ['min' => 0, 'max' => 5000, 'step' => 50, 'default' => 950, 'unit' => 'currency', 'required' => false]),
            $this->field('ev_insurance_annual', 'EV Insurance / Year', 'number', ['min' => 0, 'max' => 8000, 'step' => 50, 'default' => 1800, 'unit' => 'currency', 'required' => false]),
            $this->field('ice_insurance_annual', 'ICE Insurance / Year', 'number', ['min' => 0, 'max' => 8000, 'step' => 50, 'default' => 1450, 'unit' => 'currency', 'required' => false]),
            $this->field('ev_tax_credit', 'EV Tax Credit / Incentive (0 for 2026 federal)', 'number', ['min' => 0, 'max' => 10000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('resale_delta_ev_minus_ice', '5-yr Resale Delta (EV − ICE)', 'number', ['min' => -30000, 'max' => 30000, 'step' => 100, 'default' => -2000, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $evPrice = $this->requireNumeric($inputs, 'ev_price');
        $icePrice = $this->requireNumeric($inputs, 'ice_price');
        $miles = $this->requireNumeric($inputs, 'annual_miles');
        $elec = $this->requireNumeric($inputs, 'electricity_rate');
        $kwh100 = $this->requireNumeric($inputs, 'ev_kwh_per_100mi');
        $gas0 = $this->requireNumeric($inputs, 'gas_price');
        $gasGrowth = $this->toFloat($inputs, 'gas_growth', 3) / 100;
        $mpg = $this->requireNumeric($inputs, 'ice_mpg');
        $evMaint = $this->toFloat($inputs, 'ev_maint_annual', 450);
        $iceMaint = $this->toFloat($inputs, 'ice_maint_annual', 950);
        $evIns = $this->toFloat($inputs, 'ev_insurance_annual', 1800);
        $iceIns = $this->toFloat($inputs, 'ice_insurance_annual', 1450);
        $credit = $this->toFloat($inputs, 'ev_tax_credit', 0);
        $resaleDelta = $this->toFloat($inputs, 'resale_delta_ev_minus_ice', -2000);

        $evEnergyYr = ($miles / 100) * $kwh100 * $elec;
        $fuel5 = 0.0;
        for ($y = 0; $y < 5; $y++) {
            $fuel5 += $this->safeDivide($miles, $mpg) * $gas0 * ((1 + $gasGrowth) ** $y);
        }
        $evEnergy5 = $evEnergyYr * 5;
        $evMaint5 = $evMaint * 5;
        $iceMaint5 = $iceMaint * 5;
        $evIns5 = $evIns * 5;
        $iceIns5 = $iceIns * 5;

        $evTco = ($evPrice - $credit) + $evEnergy5 + $evMaint5 + $evIns5 - max(0, ($evPrice * 0.45) + $resaleDelta / 2);
        // Simpler TCO framing matching copy: purchase − credit + ops − relative resale benefit
        $iceResale = $icePrice * 0.45;
        $evResale = $iceResale + $resaleDelta;
        $evTco = ($evPrice - $credit) + $evEnergy5 + $evMaint5 + $evIns5 - $evResale;
        $iceTco = $icePrice + $fuel5 + $iceMaint5 + $iceIns5 - $iceResale;

        $fuelGap = $fuel5 - $evEnergy5;
        $maintGap = $iceMaint5 - $evMaint5;
        $insGap = $iceIns5 - $evIns5;

        // Flip electricity rate: solve EV TCO = ICE TCO for elec rate
        // evTco(elec) = (evPrice-credit) + 5*(miles/100)*kwh100*elec + evMaint5 + evIns5 - evResale
        $fixedEv = ($evPrice - $credit) + $evMaint5 + $evIns5 - $evResale;
        $kwh5 = 5 * ($miles / 100) * $kwh100;
        $flipRate = $kwh5 > 0 ? ($iceTco - $fixedEv) / $kwh5 : null;

        return [
            'results' => [
                'ev_5yr_tco' => $this->round($evTco),
                'ice_5yr_tco' => $this->round($iceTco),
                'tco_advantage_ev' => $this->round($iceTco - $evTco),
                'fuel_energy_gap_5yr' => $this->round($fuelGap),
                'maintenance_gap_5yr' => $this->round($maintGap),
                'insurance_gap_5yr' => $this->round($insGap),
                'resale_gap_5yr' => $this->round($resaleDelta),
                'electricity_rate_flip_point' => $flipRate === null ? 'n/a' : $this->round($flipRate, 3),
                'winner' => $evTco <= $iceTco ? 'EV' : 'ICE',
            ],
            'breakdown' => [
                'ev_energy_5yr' => $this->round($evEnergy5),
                'ice_fuel_5yr' => $this->round($fuel5),
                'note' => 'Federal §30D $7,500 EV credit expired Sep 30 2025 — default incentive $0 for 2026; enter state rebate if you qualify.',
                'anchors' => 'AAA Driving Costs, NAIC insurance deltas, KBB/BLS-style residual ~45% at year 5',
            ],
            'units' => [
                'ev_5yr_tco' => 'currency',
                'ice_5yr_tco' => 'currency',
                'tco_advantage_ev' => 'currency',
                'fuel_energy_gap_5yr' => 'currency',
                'maintenance_gap_5yr' => 'currency',
                'insurance_gap_5yr' => 'currency',
                'resale_gap_5yr' => 'currency',
                'electricity_rate_flip_point' => 'currency/kWh',
                'winner' => '',
            ],
        ];
    }
}
