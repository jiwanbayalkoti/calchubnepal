<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Climate Migration Cost Calculator
 * 10-year financial delta: move cost, COL, insurance, monetized peril risk, adjustment.
 */
class ClimateMigrationCostCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'climate_migration_cost_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('move_cost', 'One-Time Move Cost', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 15000, 'unit' => 'currency']),
            $this->field('col_delta_annual', 'Annual COL Delta (new − old)', 'number', ['min' => -50000, 'max' => 50000, 'step' => 100, 'default' => 3000, 'unit' => 'currency', 'required' => false]),
            $this->field('insurance_delta_annual', 'Annual Insurance Delta (new − old)', 'number', ['min' => -20000, 'max' => 20000, 'step' => 50, 'default' => -2400, 'unit' => 'currency', 'required' => false]),
            $this->field('current_peril_eal', 'Current Location Expected Annual Peril Loss', 'number', ['min' => 0, 'max' => 50000, 'step' => 50, 'default' => 3500, 'unit' => 'currency']),
            $this->field('new_peril_eal', 'New Location Expected Annual Peril Loss', 'number', ['min' => 0, 'max' => 50000, 'step' => 50, 'default' => 800, 'unit' => 'currency']),
            $this->field('income_delta_annual', 'Annual Income Delta (new − old)', 'number', ['min' => -100000, 'max' => 100000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('emotional_adjustment_annual', 'Emotional / Social Adjustment Cost', 'number', ['min' => 0, 'max' => 30000, 'step' => 50, 'default' => 1500, 'unit' => 'currency', 'required' => false]),
            $this->field('discount_rate', 'Discount Rate', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 5, 'unit' => '%', 'required' => false]),
            $this->field('horizon_years', 'Horizon', 'number', ['min' => 5, 'max' => 20, 'step' => 1, 'default' => 10, 'unit' => 'years']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $move = $this->requireNumeric($inputs, 'move_cost');
        $col = $this->toFloat($inputs, 'col_delta_annual', 0);
        $ins = $this->toFloat($inputs, 'insurance_delta_annual', 0);
        $ealOld = $this->requireNumeric($inputs, 'current_peril_eal');
        $ealNew = $this->requireNumeric($inputs, 'new_peril_eal');
        $income = $this->toFloat($inputs, 'income_delta_annual', 0);
        $emotion = $this->toFloat($inputs, 'emotional_adjustment_annual', 0);
        $r = $this->toFloat($inputs, 'discount_rate', 5) / 100;
        $years = (int) max(1, round($this->requireNumeric($inputs, 'horizon_years')));

        $perilSavings = $ealOld - $ealNew;
        $annualNet = -$col - $ins + $perilSavings + $income - $emotion;
        // Note: insurance_delta is (new-old); negative means cheaper insurance in new place → good
        // Wait: I defined insurance_delta as new−old. annualNet should subtract cost increases:
        // annualNet = -col_delta - insurance_delta + peril_savings + income - emotion
        // If insurance_delta = -2400 (cheaper), -(-2400)=+2400 benefit. Good.
        // Recalculate clearly:
        $annualNet = (-1 * $col) + (-1 * $ins) + $perilSavings + $income - $emotion;

        $npv = -$move;
        for ($y = 1; $y <= $years; $y++) {
            $npv += $annualNet / ((1 + $r) ** $y);
        }

        $undiscounted = -$move + ($annualNet * $years);

        return [
            'results' => [
                'annual_net_financial_delta' => $this->round($annualNet),
                'peril_risk_annual_savings' => $this->round($perilSavings),
                'ten_year_undiscounted_delta' => $this->round($undiscounted),
                'npv_over_horizon' => $this->round($npv),
                'verdict' => $npv > 0
                    ? 'Move looks financially positive over the horizon (including monetized peril)'
                    : 'Stay or renegotiate — NPV of moving is negative at these assumptions',
            ],
            'breakdown' => [
                'move_cost' => $this->round($move),
                'col_delta_annual' => $this->round($col),
                'insurance_delta_annual' => $this->round($ins),
                'income_delta_annual' => $this->round($income),
                'emotional_adjustment_annual' => $this->round($emotion),
                'horizon_years' => $years,
                'formula' => 'Annual net = −COLΔ − insuranceΔ + (EALold−EALnew) + incomeΔ − emotional cost; NPV = −move + Σ annual/(1+r)^t',
            ],
            'units' => [
                'annual_net_financial_delta' => 'currency/yr',
                'peril_risk_annual_savings' => 'currency/yr',
                'ten_year_undiscounted_delta' => 'currency',
                'npv_over_horizon' => 'currency',
                'verdict' => '',
            ],
        ];
    }
}
