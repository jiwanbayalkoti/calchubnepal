<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Property Tax Calculator
 */
class PropertyTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'property_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('assessed_value', 'Assessed Value', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 5000000, 'unit' => 'currency']),
            $this->field('tax_rate', 'Annual Tax Rate', 'number', ['min' => 0, 'max' => 10, 'step' => 0.01, 'default' => 0.5, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $tax = $this->requireNumeric($inputs, 'assessed_value') * $this->requireNumeric($inputs, 'tax_rate') / 100;
        return [
            'results' => ['annual_tax' => $this->round($tax), 'monthly_tax' => $this->round($tax / 12)],
            'breakdown' => [],
            'units' => ['annual_tax' => 'currency', 'monthly_tax' => 'currency'],
        ];
    }
}
