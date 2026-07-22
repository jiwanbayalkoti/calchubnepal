<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * 529 vs UTMA vs Roth (Kids) Calculator
 * Growth + tax + FAFSA impact; state 529 deduction; SECURE 2.0 unused-529-to-Roth.
 */
class Kids529VsUtmaVsRothCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'kids_529_vs_utma_vs_roth_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('annual_contribution', 'Annual Contribution', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 6000, 'unit' => 'currency']),
            $this->field('years', 'Years Until Use / Age 18', 'number', ['min' => 1, 'max' => 25, 'step' => 1, 'default' => 15, 'unit' => 'years']),
            $this->field('expected_return', 'Expected Annual Return', 'number', ['min' => 0, 'max' => 15, 'step' => 0.1, 'default' => 7, 'unit' => '%']),
            $this->field('parent_marginal_rate', 'Parent Marginal Tax Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 32, 'unit' => '%']),
            $this->field('state_529_deduction', 'State 529 Deduction (annual)', 'number', ['min' => 0, 'max' => 50000, 'step' => 100, 'default' => 5000, 'unit' => 'currency', 'required' => false]),
            $this->field('education_use_pct', 'Share Used for Qualified Education', 'number', ['min' => 0, 'max' => 100, 'step' => 5, 'default' => 100, 'unit' => '%', 'required' => false]),
            $this->field('fafsa_sensitive', 'FAFSA / Need-Aid Sensitive?', 'select', [
                'options' => ['yes' => 'Yes — minimize aid impact', 'no' => 'No / full-pay'],
                'default' => 'yes',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $contrib = $this->requireNumeric($inputs, 'annual_contribution');
        $years = (int) max(1, round($this->requireNumeric($inputs, 'years')));
        $r = $this->requireNumeric($inputs, 'expected_return') / 100;
        $parentRate = $this->requireNumeric($inputs, 'parent_marginal_rate') / 100;
        $stateDed = min($contrib, $this->toFloat($inputs, 'state_529_deduction', 0));
        $eduPct = $this->toFloat($inputs, 'education_use_pct', 100) / 100;
        $fafsa = $this->toString($inputs, 'fafsa_sensitive', 'yes') === 'yes';

        $fv = $this->futureValueAnnuity($contrib, $r, $years);
        $basis = $contrib * $years;
        $gain = max(0, $fv - $basis);

        // 529: tax-free if qualified; state deduction PV benefit approx
        $stateBenefit = $stateDed * $parentRate * $years; // rough cumulative
        $nonQualified = $gain * (1 - $eduPct);
        $penalty529 = $nonQualified * 0.10;
        $tax529nq = $nonQualified * $parentRate;
        $net529 = $fv - $penalty529 - $tax529nq + $stateBenefit;

        // UTMA: kiddie tax approx — unearned over ~$2.6k at parent rate (illustrative)
        $utmaTaxableGain = max(0, $gain - (2600 * $years * 0.15)); // simplified
        $utmaTax = $utmaTaxableGain * $parentRate * 0.7; // blended over years
        $netUtma = $fv - $utmaTax;

        // Roth IRA (earned income assumed for kid / parent backdoor framing): tax-free growth
        $rothContribCap = min($contrib, 7000); // illustrative annual
        $fvRoth = $this->futureValueAnnuity($rothContribCap, $r, $years);
        $netRoth = $fvRoth; // qualified / contributions accessible

        // FAFSA: parent 529 assessed ~5.64% of assets; UTMA student asset ~20%; Roth often not on FAFSA if student-owned carefully
        $fafsaHit529 = $fafsa ? $fv * 0.0564 : 0;
        $fafsaHitUtma = $fafsa ? $fv * 0.20 : 0;
        $fafsaHitRoth = $fafsa ? 0 : 0;

        $secure20RothRoom = min(35000, max(0, $fv * (1 - $eduPct))); // unused 529→Roth lifetime cap illustrative

        $winner = '529';
        $best = $net529 - $fafsaHit529;
        if ($netUtma - $fafsaHitUtma > $best) {
            $winner = 'UTMA';
            $best = $netUtma - $fafsaHitUtma;
        }
        if ($netRoth - $fafsaHitRoth > $best) {
            $winner = 'Roth IRA';
        }

        return [
            'results' => [
                'ending_529_balance' => $this->round($fv),
                'net_529_after_tax_penalty' => $this->round($net529),
                'net_utma_after_tax' => $this->round($netUtma),
                'net_roth_balance' => $this->round($netRoth),
                'fafsa_impact_529' => $this->round($fafsaHit529),
                'fafsa_impact_utma' => $this->round($fafsaHitUtma),
                'secure_20_unused_529_to_roth_room' => $this->round($secure20RothRoom),
                'recommended_vehicle' => $winner,
            ],
            'breakdown' => [
                'state_529_deduction_tax_benefit_est' => $this->round($stateBenefit),
                'note' => 'Illustrative; state deduction tables vary. SECURE 2.0 allows limited unused 529→Roth rollovers subject to rules.',
                'formula' => 'FV annuity growth; 529 NQ = 10% penalty + ordinary tax on earnings; FAFSA uses typical parent/student asset assessment rates',
            ],
            'units' => [
                'ending_529_balance' => 'currency',
                'net_529_after_tax_penalty' => 'currency',
                'net_utma_after_tax' => 'currency',
                'net_roth_balance' => 'currency',
                'fafsa_impact_529' => 'currency',
                'fafsa_impact_utma' => 'currency',
                'secure_20_unused_529_to_roth_room' => 'currency',
                'recommended_vehicle' => '',
            ],
        ];
    }

    protected function futureValueAnnuity(float $pmt, float $r, int $n): float
    {
        if ($r == 0.0) {
            return $pmt * $n;
        }

        return $pmt * (((1 + $r) ** $n - 1) / $r);
    }
}
