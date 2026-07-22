<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Lease vs Buy Car Calculator
 * Total cost both ways over your stay length, plus the break-even ownership year.
 */
class LeaseVsBuyCarCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'lease_vs_buy_car_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('stay_years', 'How Long You Will Keep the Car', 'number', ['min' => 1, 'max' => 15, 'step' => 0.5, 'default' => 3, 'unit' => 'years']),
            $this->field('purchase_price', 'Purchase Price', 'number', ['min' => 1, 'max' => 500000, 'step' => 1, 'default' => 35000, 'unit' => 'currency']),
            $this->field('down_payment', 'Down Payment (Buy)', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 5000, 'unit' => 'currency']),
            $this->field('loan_apr', 'Auto Loan APR', 'number', ['min' => 0, 'max' => 40, 'step' => 0.01, 'default' => 6.5, 'unit' => '%']),
            $this->field('loan_term_months', 'Loan Term', 'number', ['min' => 12, 'max' => 96, 'step' => 1, 'default' => 60, 'unit' => 'months']),
            $this->field('expected_resale', 'Expected Resale After Stay', 'number', ['min' => 0, 'max' => 500000, 'step' => 1, 'default' => 20000, 'unit' => 'currency']),
            $this->field('annual_buy_extra', 'Extra Annual Cost If Buying (tax/maint)', 'number', ['min' => 0, 'max' => 20000, 'step' => 1, 'default' => 800, 'unit' => 'currency', 'required' => false]),
            $this->field('lease_down', 'Lease Drive-Off / Cap Reduction', 'number', ['min' => 0, 'max' => 50000, 'step' => 1, 'default' => 2500, 'unit' => 'currency']),
            $this->field('lease_payment', 'Lease Monthly Payment', 'number', ['min' => 0, 'max' => 5000, 'step' => 1, 'default' => 429, 'unit' => 'currency']),
            $this->field('lease_term_months', 'Lease Term', 'number', ['min' => 12, 'max' => 60, 'step' => 1, 'default' => 36, 'unit' => 'months']),
            $this->field('lease_fees', 'Disposition / End Fees', 'number', ['min' => 0, 'max' => 10000, 'step' => 1, 'default' => 400, 'unit' => 'currency', 'required' => false]),
            $this->field('annual_lease_extra', 'Extra Annual Cost If Leasing', 'number', ['min' => 0, 'max' => 20000, 'step' => 1, 'default' => 200, 'unit' => 'currency', 'required' => false]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $stayYears = max(1.0, $this->requireNumeric($inputs, 'stay_years'));
        $price = $this->requireNumeric($inputs, 'purchase_price');
        $down = $this->requireNumeric($inputs, 'down_payment');
        $apr = $this->requireNumeric($inputs, 'loan_apr');
        $loanMonths = (int) max(1, round($this->requireNumeric($inputs, 'loan_term_months')));
        $resale = $this->requireNumeric($inputs, 'expected_resale');
        $buyExtra = $this->toFloat($inputs, 'annual_buy_extra', 0);
        $leaseDown = $this->requireNumeric($inputs, 'lease_down');
        $leasePayment = $this->requireNumeric($inputs, 'lease_payment');
        $leaseTerm = (int) max(1, round($this->requireNumeric($inputs, 'lease_term_months')));
        $leaseFees = $this->toFloat($inputs, 'lease_fees', 0);
        $leaseExtra = $this->toFloat($inputs, 'annual_lease_extra', 0);

        $buyCost = $this->buyCost($price, $down, $apr, $loanMonths, $stayYears, $resale, $buyExtra);
        $leaseCost = $this->leaseCost($leaseDown, $leasePayment, $leaseTerm, $stayYears, $leaseFees, $leaseExtra);

        $difference = $buyCost - $leaseCost;
        $winner = abs($difference) < 1
            ? 'Tie'
            : ($difference < 0 ? 'Buying is cheaper' : 'Leasing is cheaper');

        $breakEvenYear = $this->findBreakEvenYear(
            $price, $down, $apr, $loanMonths, $resale, $buyExtra,
            $leaseDown, $leasePayment, $leaseTerm, $leaseFees, $leaseExtra
        );

        return [
            'results' => [
                'buy_total_cost' => $this->round($buyCost),
                'lease_total_cost' => $this->round($leaseCost),
                'difference_buy_minus_lease' => $this->round($difference),
                'cheaper_option' => $winner,
                'break_even_year' => $breakEvenYear === null
                    ? 'No flip within 15 years (lease stays cheaper, or buy always cheaper)'
                    : $this->round($breakEvenYear, 1),
            ],
            'breakdown' => [
                'stay_years' => $stayYears,
                'loan_amount' => $this->round(max(0, $price - $down)),
                'note' => 'Buy cost = payments made during stay + down + extras − resale equity. Lease cost scales with how many lease cycles your stay covers.',
                'formula' => 'Break-even year = first ownership length where buy total ≤ lease total',
            ],
            'units' => [
                'buy_total_cost' => 'currency',
                'lease_total_cost' => 'currency',
                'difference_buy_minus_lease' => 'currency',
                'cheaper_option' => '',
                'break_even_year' => 'years',
            ],
        ];
    }

    protected function buyCost(
        float $price,
        float $down,
        float $apr,
        int $loanMonths,
        float $stayYears,
        float $resale,
        float $annualExtra
    ): float {
        $principal = max(0, $price - $down);
        $payment = $this->emi($principal, $apr, $loanMonths);
        $stayMonths = (int) round($stayYears * 12);
        $paymentsMade = min($stayMonths, $loanMonths) * $payment;

        // Remaining balance after stayMonths (if still financing).
        $balanceAfter = $this->balanceAfter($principal, $apr, $loanMonths, min($stayMonths, $loanMonths));
        // Net equity recovered at sale: resale minus remaining loan.
        $equity = $resale - $balanceAfter;

        return $down + $paymentsMade + ($annualExtra * $stayYears) - $equity;
    }

    protected function leaseCost(
        float $leaseDown,
        float $leasePayment,
        int $leaseTerm,
        float $stayYears,
        float $leaseFees,
        float $annualExtra
    ): float {
        $stayMonths = $stayYears * 12;
        // How many full lease cycles + partial months.
        $cycles = (int) floor($stayMonths / $leaseTerm);
        $remainder = $stayMonths - ($cycles * $leaseTerm);

        $cost = 0.0;
        for ($i = 0; $i < $cycles; $i++) {
            $cost += $leaseDown + ($leasePayment * $leaseTerm) + $leaseFees;
        }
        if ($remainder > 0) {
            // Partial final cycle: prorate drive-off + payments; charge disposition if ending.
            $cost += $leaseDown + ($leasePayment * $remainder) + $leaseFees;
        } elseif ($cycles > 0) {
            // Already counted fees per cycle.
        }

        // If stay is shorter than one term, only one partial cycle above.
        if ($cycles === 0 && $remainder <= 0) {
            $cost = $leaseDown + $leaseFees;
        }

        return $cost + ($annualExtra * $stayYears);
    }

    protected function findBreakEvenYear(
        float $price,
        float $down,
        float $apr,
        int $loanMonths,
        float $resaleAtStay,
        float $buyExtra,
        float $leaseDown,
        float $leasePayment,
        int $leaseTerm,
        float $leaseFees,
        float $leaseExtra
    ): ?float {
        // Scale resale roughly with years (simple straight-line toward 20% of price at year 10).
        $prevLeaseCheaper = null;
        for ($y = 1.0; $y <= 15.0; $y += 0.5) {
            $resale = max(0, $price - (($price - ($price * 0.2)) * min(1, $y / 10)));
            // Prefer user resale when evaluating near their stated stay; otherwise decay model.
            if (abs($y - 3) < 0.01) {
                $resale = $resaleAtStay;
            }
            $buy = $this->buyCost($price, $down, $apr, $loanMonths, $y, $resale, $buyExtra);
            $lease = $this->leaseCost($leaseDown, $leasePayment, $leaseTerm, $y, $leaseFees, $leaseExtra);
            $buyCheaper = $buy <= $lease;
            if ($prevLeaseCheaper === true && $buyCheaper) {
                return $y;
            }
            if ($prevLeaseCheaper === null && $buyCheaper && $y <= 1.0) {
                return 1.0;
            }
            $prevLeaseCheaper = ! $buyCheaper;
        }

        return null;
    }

    protected function emi(float $principal, float $annualRatePct, int $months): float
    {
        if ($principal <= 0 || $months <= 0) {
            return 0.0;
        }
        $r = $annualRatePct / 12 / 100;
        if ($r == 0.0) {
            return $principal / $months;
        }
        $factor = (1 + $r) ** $months;

        return $principal * $r * $factor / ($factor - 1);
    }

    protected function balanceAfter(float $principal, float $annualRatePct, int $termMonths, int $paidMonths): float
    {
        if ($principal <= 0 || $paidMonths >= $termMonths) {
            return 0.0;
        }
        $r = $annualRatePct / 12 / 100;
        if ($r == 0.0) {
            return $principal * (1 - ($paidMonths / $termMonths));
        }
        $emi = $this->emi($principal, $annualRatePct, $termMonths);
        $balance = $principal;
        for ($i = 0; $i < $paidMonths; $i++) {
            $interest = $balance * $r;
            $principalPaid = $emi - $interest;
            $balance -= $principalPaid;
        }

        return max(0, $balance);
    }
}
