<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * NEPSE equity transaction cost estimator using SEBON broker commission
 * ceilings effective Jestha 2081 (May 2024), plus SEBON fee, DP charge
 * and optional capital gains tax on sell.
 *
 * Commission slabs are progressive (each band applies only to the portion
 * of the trade that falls in that band).
 */
class NepseBrokerageCalculator extends AbstractCalculatorHandler
{
    /** SEBON regulatory fee on transaction value (both sides). */
    protected const SEBON_FEE_RATE = 0.00015;

    /** CDSC DP charge per company per settlement (NPR). */
    protected const DP_FEE = 25.0;

    /** Minimum broker commission (NPR). */
    protected const MIN_BROKERAGE = 10.0;

    /**
     * Progressive equity commission slabs: [width|null, rate].
     *
     * @var array<int, array{0: float|null, 1: float}>
     */
    protected const BROKERAGE_SLABS = [
        [50000.0, 0.0036],
        [450000.0, 0.0033],      // 50k–500k
        [1500000.0, 0.00306],    // 500k–2M (0.306%)
        [8000000.0, 0.0027],     // 2M–10M
        [null, 0.00243],         // above 10M
    ];

    public function key(): string
    {
        return 'nepse_brokerage_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('transaction_amount', 'Transaction Amount', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 100000,
                'unit' => 'NPR',
            ]),
            $this->field('side', 'Side', 'select', [
                'options' => ['buy' => 'Buy', 'sell' => 'Sell'],
                'default' => 'buy',
            ]),
            $this->field('include_dp', 'Include DP Charge (NPR 25)', 'boolean', [
                'default' => true,
                'required' => false,
            ]),
            $this->field('capital_gain', 'Capital Gain (Sell only)', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 0,
                'unit' => 'NPR',
                'required' => false,
            ]),
            $this->field('cgt_rate', 'CGT Rate (Sell)', 'select', [
                'options' => [
                    '0' => 'None / skip CGT',
                    '5' => '5% (held > 365 days, individual)',
                    '7.5' => '7.5% (held ≤ 365 days, individual)',
                    '10' => '10% (entity / other)',
                ],
                'default' => '0',
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'transaction_amount');
        $side = $this->toString($inputs, 'side', 'buy') === 'sell' ? 'sell' : 'buy';
        $includeDp = $this->toBool($inputs, 'include_dp', true);
        $capitalGain = max(0.0, $this->toFloat($inputs, 'capital_gain', 0));
        $cgtRate = (float) $this->toString($inputs, 'cgt_rate', '0');

        [$brokerage, $brokerageSlabs] = $this->progressiveBrokerage($amount);
        $sebon = $amount * self::SEBON_FEE_RATE;
        $dp = $includeDp ? self::DP_FEE : 0.0;
        $cgt = ($side === 'sell' && $cgtRate > 0) ? $capitalGain * ($cgtRate / 100) : 0.0;

        $totalCharges = $brokerage + $sebon + $dp + $cgt;
        $net = $side === 'buy' ? $amount + $brokerage + $sebon + $dp : $amount - $brokerage - $sebon - $dp - $cgt;

        return [
            'results' => [
                'brokerage' => $this->round($brokerage, 2),
                'sebon_fee' => $this->round($sebon, 2),
                'dp_fee' => $this->round($dp, 2),
                'capital_gains_tax' => $this->round($cgt, 2),
                'total_charges' => $this->round($totalCharges, 2),
                'net_amount' => $this->round($net, 2),
                'effective_cost_percent' => $this->round($this->percentageOf($brokerage + $sebon + $dp, max($amount, 1)), 4),
            ],
            'breakdown' => [
                'rule_basis' => 'SEBON equity commission ceilings (Jestha 2081 / May 2024) + SEBON 0.015% + DP NPR 25',
                'side' => $side === 'buy' ? 'Buy' : 'Sell',
                'brokerage_slabs' => $brokerageSlabs,
                'note' => 'Rates are regulatory ceilings — some brokers charge less. Confirm DP applicability and CGT holding period with your broker/IRD.',
            ],
            'units' => [
                'brokerage' => 'NPR',
                'sebon_fee' => 'NPR',
                'dp_fee' => 'NPR',
                'capital_gains_tax' => 'NPR',
                'total_charges' => 'NPR',
                'net_amount' => 'NPR',
                'effective_cost_percent' => '%',
            ],
        ];
    }

    /**
     * @return array{0: float, 1: array<int, array<string, mixed>>}
     */
    protected function progressiveBrokerage(float $amount): array
    {
        $remaining = max(0.0, $amount);
        $total = 0.0;
        $rangeStart = 0.0;
        $rows = [];

        foreach (self::BROKERAGE_SLABS as [$width, $rate]) {
            $chunk = $width === null ? $remaining : min($remaining, $width);
            $fee = $chunk * $rate;
            $total += $fee;

            $rangeEnd = $width === null ? null : $rangeStart + $width;
            if ($chunk > 0 || $amount === 0.0) {
                $rows[] = [
                    'range' => $this->formatRange($rangeStart, $rangeEnd),
                    'rate_percent' => $this->round($rate * 100, 3),
                    'taxable_in_slab' => $this->round($chunk),
                    'tax' => $this->round($fee, 2),
                ];
            }

            $remaining -= $chunk;
            $rangeStart += $width ?? 0;
            if ($remaining <= 0.00001) {
                break;
            }
        }

        if ($amount > 0 && $total < self::MIN_BROKERAGE) {
            $total = self::MIN_BROKERAGE;
            $rows = [[
                'range' => 'Minimum commission (SEBON)',
                'rate_percent' => 'min',
                'taxable_in_slab' => $this->round($amount),
                'tax' => $this->round(self::MIN_BROKERAGE, 2),
            ]];
        }

        return [$total, $rows];
    }

    protected function formatRange(float $from, ?float $to): string
    {
        $fromLabel = number_format($from, 0, '.', ',');
        if ($to === null) {
            return 'Above NPR '.$fromLabel;
        }

        return 'NPR '.$fromLabel.' – '.number_format($to, 0, '.', ',');
    }
}
