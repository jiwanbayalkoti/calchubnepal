<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * TDS Calculator
 */
class TdsCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'tds_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('amount', 'Payment Amount', 'number', ['min' => 0, 'max' => 1000000000, 'step' => 0.01, 'default' => 100000, 'unit' => 'currency']),
            $this->field('tds_rate', 'TDS Rate', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 10, 'unit' => '%']),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'tds_rate');
        $tds = $amount * $rate / 100;
        return [
            'results' => [
                'tds_amount' => $this->round($tds),
                'net_payable' => $this->round($amount - $tds),
            ],
            'breakdown' => ['gross_amount' => $amount, 'rate_percent' => $rate],
            'units' => ['tds_amount' => 'currency', 'net_payable' => 'currency'],
        ];
    }
}
