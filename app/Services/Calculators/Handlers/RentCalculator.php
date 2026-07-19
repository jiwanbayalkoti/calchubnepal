<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Rent Affordability Calculator
 */
class RentCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'rent_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('monthly_income', 'Monthly Income', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 80000, 'unit' => 'currency']),
            $this->field('rent_ratio', 'Max Rent % of Income', 'number', ['min' => 0, 'max' => 60, 'step' => 0.01, 'default' => 30, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $income = $this->requireNumeric($inputs, 'monthly_income');
        $ratio = $this->requireNumeric($inputs, 'rent_ratio') / 100;
        $rent = $income * $ratio;
        return [
            'results' => [
                'max_monthly_rent' => $this->round($rent),
                'max_annual_rent' => $this->round($rent * 12),
            ],
            'breakdown' => ['rule' => 'Income × rent ratio'],
            'units' => ['max_monthly_rent' => 'currency', 'max_annual_rent' => 'currency'],
        ];
    }
}
