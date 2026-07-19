<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;
use InvalidArgumentException;

/**
 * Nepal personal income tax estimator for FY 2083/84.
 *
 * Progressive annual taxable-income slabs:
 *   0 – 10,00,000     → 1% Social Security Tax (SST)*
 *   10,00,001 – 15,00,000 → 10%
 *   15,00,001 – 25,00,000 → 20%
 *   25,00,001 – 40,00,000 → 27%
 *   Above 40,00,000   → 29%
 *
 * *Eligible SSF / approved retirement-fund contributors may be exempt
 * from the 1% SST on the first Rs. 10 lakh.
 */
class NepalIncomeTaxCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'nepal_income_tax_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('period', 'Income Period', 'select', [
                'options' => [
                    'monthly' => 'Monthly Income',
                    'annual' => 'Annual Income',
                ],
                'default' => 'monthly',
            ]),
            $this->field('income', 'Taxable Income', 'number', [
                'min' => 0,
                'max' => 1000000000,
                'step' => 0.01,
                'default' => 50000,
                'unit' => 'NPR',
            ]),
            $this->field('ssf_contributor', 'SSF / approved retirement fund contributor (waive 1% SST)', 'boolean', [
                'default' => false,
                'required' => false,
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $period = $this->toString($inputs, 'period', 'monthly');
        if (! in_array($period, ['monthly', 'annual'], true)) {
            throw new InvalidArgumentException('Income period must be monthly or annual.');
        }

        $incomeInput = $this->requireNumeric($inputs, 'income');
        $ssfContributor = $this->toBool($inputs, 'ssf_contributor', false);

        $annualIncome = $period === 'monthly' ? $incomeInput * 12 : $incomeInput;
        $slabs = $this->slabs($ssfContributor);

        $remaining = max(0.0, $annualIncome);
        $tax = 0.0;
        $rangeStart = 0.0;
        $slabBreakdown = [];

        foreach ($slabs as [$width, $ratePercent, $label, $maxTaxInBand]) {
            $chunk = $width === null ? $remaining : min($remaining, $width);
            $taxInSlab = $chunk * ($ratePercent / 100);
            $tax += $taxInSlab;

            $rangeEnd = $width === null ? null : $rangeStart + $width;

            if ($chunk > 0 || $annualIncome === 0.0) {
                $slabBreakdown[] = [
                    'range' => $this->formatRange($rangeStart, $rangeEnd),
                    'rate_percent' => $ratePercent,
                    'taxable_in_slab' => $this->round($chunk),
                    'tax' => $this->round($taxInSlab),
                    'max_tax_in_band' => $maxTaxInBand === null ? 'No upper limit' : number_format($maxTaxInBand, 0, '.', ','),
                    'band' => $label,
                ];
            }

            $remaining -= $chunk;
            $rangeStart += $width ?? 0;

            if ($remaining <= 0.00001) {
                break;
            }
        }

        $monthlyTax = $tax / 12;
        $netAnnual = $annualIncome - $tax;
        $effectiveRate = $this->percentageOf($tax, max($annualIncome, 1));

        return [
            'results' => [
                'annual_taxable_income' => $this->round($annualIncome),
                'estimated_annual_tax' => $this->round($tax),
                'estimated_monthly_tax' => $this->round($monthlyTax),
                'net_annual_income' => $this->round($netAnnual),
                'net_monthly_income' => $this->round($netAnnual / 12),
                'effective_rate_percent' => $this->round($effectiveRate, 2),
            ],
            'breakdown' => [
                'fiscal_year' => 'FY 2083/84',
                'ssf_sst_waived' => $ssfContributor
                    ? 'Yes (1% SST on first Rs. 10 lakh waived)'
                    : 'No',
                'slabs' => $slabBreakdown,
                'note' => 'Based on published FY 2083/84 progressive slabs. Estimate only — verify with IRD / a tax advisor for deductions and exemptions.',
            ],
            'units' => [
                'annual_taxable_income' => 'NPR',
                'estimated_annual_tax' => 'NPR',
                'estimated_monthly_tax' => 'NPR',
                'net_annual_income' => 'NPR',
                'net_monthly_income' => 'NPR',
                'effective_rate_percent' => '%',
            ],
        ];
    }

    /**
     * Width-based progressive bands matching FY 2083/84 table.
     *
     * @return array<int, array{0: float|null, 1: float, 2: string, 3: float|null}>
     */
    protected function slabs(bool $ssfContributor): array
    {
        $firstRate = $ssfContributor ? 0.0 : 1.0;
        $firstMax = $ssfContributor ? 0.0 : 10000.0;

        return [
            [1000000.0, $firstRate, 'Up to Rs. 10,00,000 (SST)', $firstMax],
            [500000.0, 10.0, 'Rs. 10,00,001 – 15,00,000', 50000.0],
            [1000000.0, 20.0, 'Rs. 15,00,001 – 25,00,000', 200000.0],
            [1500000.0, 27.0, 'Rs. 25,00,001 – 40,00,000', 405000.0],
            [null, 29.0, 'Above Rs. 40,00,000', null],
        ];
    }

    protected function formatRange(float $from, ?float $to): string
    {
        $fromLabel = number_format($from, 0, '.', ',');

        if ($to === null) {
            return 'Above NPR '.$fromLabel;
        }

        if ($from <= 0) {
            return 'Up to NPR '.number_format($to, 0, '.', ',');
        }

        return 'NPR '.number_format($from + 1, 0, '.', ',').' – '.number_format($to, 0, '.', ',');
    }
}
