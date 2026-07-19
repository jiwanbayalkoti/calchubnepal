<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Passport Fee Estimator
 */
class PassportFeeCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'passport_fee_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('pages', 'Booklet', 'select', ['options' => ['36' => '36 pages', '60' => '60 pages'], 'default' => '36']),
            $this->field('urgency', 'Service', 'select', ['options' => ['normal' => 'Normal', 'urgent' => 'Urgent'], 'default' => 'normal']),
        ];
    }

    public function calculate(array $inputs): array
    {
        // Illustrative fee table — update when DoFE fees change
        $base = match ($this->toString($inputs, 'pages', '36')) {
            '60' => 10000,
            default => 5000,
        };
        if ($this->toString($inputs, 'urgency', 'normal') === 'urgent') {
            $base *= 2;
        }
        return [
            'results' => ['estimated_fee_npr' => $base],
            'breakdown' => ['note' => 'Indicative only — confirm on official DoFE / Department of Passport portal'],
            'units' => ['estimated_fee_npr' => 'NPR'],
        ];
    }
}
