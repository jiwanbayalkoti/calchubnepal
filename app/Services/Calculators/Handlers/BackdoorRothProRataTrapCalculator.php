<?php

namespace App\Services\Calculators\Handlers;

use App\Services\Calculators\AbstractCalculatorHandler;

/**
 * Backdoor Roth + Pro-Rata Trap Calculator
 * Pro-rata tax if Trad IRA balances aggregate; mitigate by rolling to 401(k).
 */
class BackdoorRothProRataTrapCalculator extends AbstractCalculatorHandler
{
    public function key(): string
    {
        return 'backdoor_roth_pro_rata_trap_calculator';
    }

    public function inputSchema(): array
    {
        return [
            $this->field('nondeductible_conversion', 'Non-Deductible IRA Contribution / Conversion Amount', 'number', ['min' => 0, 'max' => 100000, 'step' => 100, 'default' => 7000, 'unit' => 'currency']),
            $this->field('trad_ira_pretax', 'Traditional IRA Pre-Tax Balance', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 80000, 'unit' => 'currency']),
            $this->field('trad_ira_basis', 'Traditional IRA After-Tax Basis (Form 8606)', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('sep_simple_balances', 'SEP/SIMPLE IRA Balances (aggregate)', 'number', ['min' => 0, 'max' => 5000000, 'step' => 100, 'default' => 0, 'unit' => 'currency', 'required' => false]),
            $this->field('marginal_rate', 'Ordinary Marginal Rate', 'number', ['min' => 0, 'max' => 50, 'step' => 1, 'default' => 32, 'unit' => '%']),
            $this->field('can_roll_to_401k', 'Can Roll Pre-Tax IRA into 401(k)?', 'select', [
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'default' => 'yes',
            ]),
        ];
    }

    public function calculate(array $inputs): array
    {
        $conversion = $this->requireNumeric($inputs, 'nondeductible_conversion');
        $pretax = $this->requireNumeric($inputs, 'trad_ira_pretax');
        $basis = $this->toFloat($inputs, 'trad_ira_basis', 0);
        $sep = $this->toFloat($inputs, 'sep_simple_balances', 0);
        $rate = $this->requireNumeric($inputs, 'marginal_rate') / 100;
        $canRoll = $this->toString($inputs, 'can_roll_to_401k', 'yes') === 'yes';

        // Year-end aggregation: after contribution, total IRA = pretax + basis + sep + new nondeductible
        $totalIra = $pretax + $basis + $sep + $conversion;
        $totalBasis = $basis + $conversion;
        $nontaxableRatio = $totalIra > 0 ? min(1, $totalBasis / $totalIra) : 1;
        $nontaxable = $conversion * $nontaxableRatio;
        $taxable = $conversion - $nontaxable;
        $taxBill = $taxable * $rate;

        // Mitigated: roll pretax+sep to 401k first, then convert only nondeductible
        $mitigatedTaxable = 0.0;
        $mitigatedTax = 0.0;
        if ($canRoll) {
            $mitigatedTaxable = 0.0; // clean backdoor
            $mitigatedTax = 0.0;
        }

        return [
            'results' => [
                'pro_rata_nontaxable_ratio_pct' => $this->round($nontaxableRatio * 100, 2),
                'taxable_portion_of_conversion' => $this->round($taxable),
                'pro_rata_tax_bill' => $this->round($taxBill),
                'tax_if_roll_pretax_to_401k_first' => $this->round($mitigatedTax),
                'tax_saved_by_mitigation' => $this->round($taxBill - $mitigatedTax),
                'mitigation' => $canRoll
                    ? 'Roll pre-tax IRA / SEP / SIMPLE into 401(k) before backdoor conversion to avoid pro-rata'
                    : 'Without a 401(k) landing spot, pro-rata aggregation will tax part of the conversion',
            ],
            'breakdown' => [
                'aggregated_ira_balance' => $this->round($totalIra),
                'aggregated_basis' => $this->round($totalBasis),
                'formula' => 'Nontaxable % = total basis ÷ total IRA balances (Form 8606 pro-rata); taxable = conversion × (1 − nontaxable %)',
            ],
            'units' => [
                'pro_rata_nontaxable_ratio_pct' => '%',
                'taxable_portion_of_conversion' => 'currency',
                'pro_rata_tax_bill' => 'currency',
                'tax_if_roll_pretax_to_401k_first' => 'currency',
                'tax_saved_by_mitigation' => 'currency',
                'mitigation' => '',
            ],
        ];
    }
}
