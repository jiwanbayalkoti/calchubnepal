<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Return on Investment calculator: computes total ROI% and, when a
 * holding period is provided, the annualized (CAGR) return.
 */
class RoiCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'roi_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('initial_investment', 'Initial Investment', 'number', ['unit' => 'currency', 'min' => 0.01, 'max' => 1000000000, 'step' => 0.01, 'default' => 10000]),
            $this->field('final_value', 'Final Value', 'number', ['unit' => 'currency', 'min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 15000]),
            $this->field('investment_duration_years', 'Investment Duration', 'number', ['unit' => 'years', 'min' => 0.01, 'max' => 100, 'step' => 0.01, 'default' => 1, 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $initial = $this->requireNumeric($inputs, 'initial_investment');
        $finalValue = $this->requireNumeric($inputs, 'final_value');
        $years = $this->toFloat($inputs, 'investment_duration_years', 1);

        $netGain = $finalValue - $initial;
        $roiPercent = $this->percentageOf($netGain, $initial);

        $annualizedRoi = null;
        if ($years > 0 && $initial > 0 && $finalValue >= 0) {
            $annualizedRoi = ((($finalValue / $initial) ** (1 / $years)) - 1) * 100;
        }

        return [
            'results' => array_filter([
                'net_gain' => $this->round($netGain),
                'roi_percent' => $this->round($roiPercent),
                'annualized_roi_percent' => $annualizedRoi !== null ? $this->round($annualizedRoi) : null,
            ], fn ($value) => $value !== null),
            'breakdown' => [
                'initial_investment' => $this->round($initial),
                'final_value' => $this->round($finalValue),
                'duration_years' => $years,
            ],
            'units' => [
                'net_gain' => 'currency',
                'roi_percent' => '%',
                'annualized_roi_percent' => '%',
            ],
        ];
    }
}
