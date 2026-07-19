<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Progressive (slab-based) income tax estimator.
 *
 * Supports a generic educational slab table and Nepal FY 2083/84 personal
 * income-tax bands. Prefer the dedicated Nepal Income Tax calculator for
 * the full SSF / monthly-annual UX.
 */
class SalaryTaxCalculator extends AbstractCalculatorHandler
{
    /**
     * @var array<int, array{0: float|null, 1: float}>
     */
    protected const GENERIC_SLABS = [
        [300000, 0],
        [600000, 5],
        [900000, 10],
        [1200000, 15],
        [1500000, 20],
        [null, 30],
    ];

    /**
     * Nepal FY 2083/84 width-based slabs: [width|null, rate%].
     *
     * @var array<int, array{0: float|null, 1: float}>
     */
    protected const NEPAL_FY_2083_84_SLABS = [
        [1000000, 1],
        [500000, 10],
        [1000000, 20],
        [1500000, 27],
        [null, 29],
    ];

    public function key(): string
    {
        return 'salary_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('jurisdiction', 'Tax Table', 'select', [
                'options' => [
                    'generic' => 'Generic progressive (illustrative)',
                    'nepal_fy_2083_84' => 'Nepal FY 2083/84',
                ],
                'default' => 'nepal_fy_2083_84',
            ]),
            $this->field('period', 'Income Period', 'select', [
                'options' => [
                    'monthly' => 'Monthly',
                    'annual' => 'Annual',
                ],
                'default' => 'monthly',
            ]),
            $this->field('income', 'Taxable Income', 'number', [
                'unit' => 'currency',
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 50000,
            ]),
            $this->field('deductions', 'Annual Deductions', 'number', [
                'unit' => 'currency',
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 0,
                'required' => false,
            ]),
            $this->field('cess_percent', 'Cess % (generic table only)', 'number', [
                'unit' => '%',
                'min' => 0,
                'max' => 20,
                'step' => 0.01,
                'default' => 0,
                'required' => false,
            ]),
            $this->field('ssf_contributor', 'SSF Contributor (Nepal: waive 1% SST)', 'boolean', [
                'default' => false,
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $jurisdiction = $this->toString($inputs, 'jurisdiction', 'nepal_fy_2083_84');
        if ($jurisdiction === 'nepal_fy_2082_83') {
            $jurisdiction = 'nepal_fy_2083_84';
        }
        $period = $this->toString($inputs, 'period', 'monthly');
        $incomeInput = $this->requireNumeric($inputs, 'income');
        $deductions = $this->toFloat($inputs, 'deductions', 0);
        $cessPercent = $jurisdiction === 'generic' ? $this->toFloat($inputs, 'cess_percent', 0) : 0.0;
        $ssf = $this->toBool($inputs, 'ssf_contributor', false);

        $annualIncome = $period === 'monthly' ? $incomeInput * 12 : $incomeInput;
        $taxableIncome = max(0, $annualIncome - $deductions);

        if ($jurisdiction === 'nepal_fy_2083_84') {
            $slabs = self::NEPAL_FY_2083_84_SLABS;
            if ($ssf) {
                $slabs[0][1] = 0;
            }
            [$tax, $slabBreakdown] = $this->taxFromWidthSlabs($taxableIncome, $slabs);
            $tableLabel = 'Nepal FY 2083/84';
        } else {
            [$tax, $slabBreakdown] = $this->taxFromUpperBoundSlabs($taxableIncome, self::GENERIC_SLABS);
            $tableLabel = 'Generic progressive';
        }

        $cessAmount = $tax * $cessPercent / 100;
        $totalTax = $tax + $cessAmount;
        $netIncome = $taxableIncome - $totalTax;
        $effectiveRate = $this->percentageOf($totalTax, max($taxableIncome, 1));

        return [
            'results' => [
                'annual_taxable_income' => $this->round($taxableIncome),
                'total_tax' => $this->round($totalTax),
                'monthly_tax' => $this->round($totalTax / 12),
                'net_annual_income' => $this->round($netIncome),
                'net_monthly_income' => $this->round($netIncome / 12),
                'effective_tax_rate' => $this->round($effectiveRate, 2),
            ],
            'breakdown' => [
                'tax_table' => $tableLabel,
                'base_tax' => $this->round($tax),
                'cess_amount' => $this->round($cessAmount),
                'slabs' => $slabBreakdown,
                'note' => $jurisdiction === 'nepal_fy_2083_84'
                    ? 'Uses FY 2083/84 progressive slabs. For the dedicated Nepal UX, open Nepal Income Tax calculator.'
                    : 'Illustrative generic slabs — not a specific country statute.',
            ],
            'units' => [
                'annual_taxable_income' => 'currency',
                'total_tax' => 'currency',
                'monthly_tax' => 'currency',
                'net_annual_income' => 'currency',
                'net_monthly_income' => 'currency',
                'effective_tax_rate' => '%',
            ],
        ];
    }

    /**
     * @param  array<int, array{0: float|null, 1: float}>  $slabs
     * @return array{0: float, 1: array<int, array<string, mixed>>}
     */
    protected function taxFromWidthSlabs(float $income, array $slabs): array
    {
        $remaining = $income;
        $tax = 0.0;
        $rangeStart = 0.0;
        $rows = [];

        foreach ($slabs as [$width, $rate]) {
            $chunk = $width === null ? $remaining : min($remaining, $width);
            $taxIn = $chunk * $rate / 100;
            $tax += $taxIn;
            $rangeEnd = $width === null ? null : $rangeStart + $width;

            if ($chunk > 0 || $income === 0.0) {
                $rows[] = [
                    'range' => $this->formatRange($rangeStart, $rangeEnd),
                    'rate_percent' => $rate,
                    'taxable_in_slab' => $this->round($chunk),
                    'tax' => $this->round($taxIn),
                ];
            }

            $remaining -= $chunk;
            $rangeStart += $width ?? 0;
            if ($remaining <= 0.00001) {
                break;
            }
        }

        return [$tax, $rows];
    }

    /**
     * @param  array<int, array{0: float|null, 1: float}>  $slabs
     * @return array{0: float, 1: array<int, array<string, mixed>>}
     */
    protected function taxFromUpperBoundSlabs(float $taxableIncome, array $slabs): array
    {
        $tax = 0.0;
        $lowerBound = 0.0;
        $rows = [];

        foreach ($slabs as [$upperBound, $rate]) {
            $bracketTop = $upperBound === null ? $taxableIncome : min($taxableIncome, $upperBound);

            if ($bracketTop > $lowerBound) {
                $amountInBracket = $bracketTop - $lowerBound;
                $taxInBracket = $amountInBracket * $rate / 100;
                $tax += $taxInBracket;
                $rows[] = [
                    'range' => $this->formatRange($lowerBound, $upperBound),
                    'rate_percent' => $rate,
                    'taxable_in_slab' => $this->round($amountInBracket),
                    'tax' => $this->round($taxInBracket),
                ];
            }

            if ($upperBound === null || $taxableIncome <= $upperBound) {
                break;
            }

            $lowerBound = $upperBound;
        }

        return [$tax, $rows];
    }

    protected function formatRange(float $from, ?float $to): string
    {
        $fromLabel = number_format($from, 0, '.', ',');
        if ($to === null) {
            return 'Above '.$fromLabel;
        }

        return $fromLabel.' – '.number_format($to, 0, '.', ',');
    }
}
