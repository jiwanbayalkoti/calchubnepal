<?php

namespace App\Services\Calculators;

use Illuminate\Support\Carbon;
use InvalidArgumentException;

/**
 * Generic, configuration-driven calculator handler.
 *
 * Instead of one bespoke PHP class per calculator, this handler reads a
 * declarative definition (fields + an "engine" name) from
 * app/Services/Calculators/Catalog/definitions.php and dispatches
 * calculate() to the matching protected *Engine() method below. This lets
 * every catalog stub calculator produce real, transparent results without
 * requiring a dedicated handler class for each one.
 */
class ConfigurableCalculatorHandler extends AbstractCalculatorHandler
{
    /**
     * @param  array<string, mixed>  $definition
     */
    public function __construct(protected string $formulaKey, protected array $definition) {}

    public function key(): string
    {
        return $this->formulaKey;
    }

    public function inputSchema(): array
    {
        $schema = [];

        foreach ($this->definition['fields'] ?? [] as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            $label = $field['label'] ?? ucwords(str_replace('_', ' ', $name));
            $type = $field['type'] ?? 'number';
            $extra = array_diff_key($field, ['name' => true, 'label' => true, 'type' => true]);

            $schema[] = $this->field($name, $label, $type, $extra);
        }

        return $schema;
    }

    /**
     * @param  array<string, mixed>  $inputs
     */
    public function calculate(array $inputs): array
    {
        $engine = $this->definition['engine'] ?? null;

        if (! $engine) {
            throw new InvalidArgumentException("No engine configured for formula [{$this->formulaKey}].");
        }

        return match ($engine) {
            'pension_lump_vs_annuity' => $this->pensionLumpVsAnnuityEngine($inputs),
            'loan_emi' => $this->loanEmiEngine($inputs),
            'amortization' => $this->amortizationEngine($inputs),
            'apr_to_apy' => $this->aprToApyEngine($inputs),
            'compound_savings' => $this->compoundSavingsEngine($inputs),
            'simple_roi' => $this->simpleRoiEngine($inputs),
            'net_worth' => $this->netWorthEngine($inputs),
            'budget_503020' => $this->budget503020Engine($inputs),
            'two_cost_compare' => $this->twoCostCompareEngine($inputs),
            'income_multiple_need' => $this->incomeMultipleNeedEngine($inputs),
            'premium_vs_expected' => $this->premiumVsExpectedEngine($inputs),
            'unit_convert' => $this->unitConvertEngine($inputs),
            'paycheck' => $this->paycheckEngine($inputs),
            'credit_card_payoff' => $this->creditCardPayoffEngine($inputs),
            'affordability' => $this->affordabilityEngine($inputs),
            'subscription_audit' => $this->subscriptionAuditEngine($inputs),
            'lifetime_cost' => $this->lifetimeCostEngine($inputs),
            'days_between' => $this->daysBetweenEngine($inputs),
            'date_offset' => $this->dateOffsetEngine($inputs),
            'average_mean' => $this->averageMeanEngine($inputs),
            'area_rect' => $this->areaRectEngine($inputs),
            'volume_box' => $this->volumeBoxEngine($inputs),
            'aspect_ratio' => $this->aspectRatioEngine($inputs),
            'temp_convert' => $this->tempConvertEngine($inputs),
            'word_counter' => $this->wordCounterEngine($inputs),
            'fire_success' => $this->fireSuccessEngine($inputs),
            'pmi_removal' => $this->pmiRemovalEngine($inputs),
            'ss_breakeven' => $this->ssBreakevenEngine($inputs),
            'rmd' => $this->rmdEngine($inputs),
            'a1c' => $this->a1cEngine($inputs),
            'bac' => $this->bacEngine($inputs),
            'macros' => $this->macrosEngine($inputs),
            'sleep_cycles' => $this->sleepCyclesEngine($inputs),
            'dog_human_age' => $this->dogHumanAgeEngine($inputs),
            'token_cost' => $this->tokenCostEngine($inputs),
            'meeting_cost' => $this->meetingCostEngine($inputs),
            'raise_impact' => $this->raiseImpactEngine($inputs),
            'salary_hourly' => $this->salaryHourlyEngine($inputs),
            'generic_growth' => $this->genericGrowthEngine($inputs),
            'percent_of' => $this->percentOfEngine($inputs),
            'lottery_annuity' => $this->lotteryAnnuityEngine($inputs),
            'roman_numeral' => $this->romanNumeralEngine($inputs),
            'shoe_size' => $this->shoeSizeEngine($inputs),
            'multi_length_convert' => $this->multiLengthConvertEngine($inputs),
            default => throw new InvalidArgumentException("Unknown calculator engine [{$engine}] for formula [{$this->formulaKey}]."),
        };
    }

    /**
     * ---------------------------------------------------------------
     * 1. Pension lump-sum vs monthly annuity NPV decision.
     * ---------------------------------------------------------------
     */
    protected function pensionLumpVsAnnuityEngine(array $inputs): array
    {
        $lumpSum = $this->requireNumeric($inputs, 'lump_sum');
        $monthlyPension = $this->requireNumeric($inputs, 'monthly_pension');
        $currentAge = $this->requireNumeric($inputs, 'current_age');
        $lifeExpectancyAge = $this->requireNumeric($inputs, 'life_expectancy_age');
        $discountRatePercent = $this->requireNumeric($inputs, 'discount_rate_percent');
        $colaPercent = $this->toFloat($inputs, 'cola_percent', 0);

        $r = $discountRatePercent / 12 / 100;
        $g = $colaPercent / 12 / 100;

        $months = max(0, ($lifeExpectancyAge - $currentAge) * 12);
        $npv = $this->growingAnnuityPv($monthlyPension, $r, $g, $months);
        $wealthGap = $npv - $lumpSum;

        $breakevenAge = $currentAge + $this->safeDivide($lumpSum, $monthlyPension * 12);

        $recommendation = $wealthGap > 0
            ? sprintf(
                'Taking the monthly annuity has a present value about %s higher than the lump sum, assuming you live to age %s. If you expect to live at least that long, the annuity is likely the better financial choice.',
                $this->money($wealthGap),
                $this->round($lifeExpectancyAge, 0)
            )
            : sprintf(
                'The lump sum has a present value about %s higher than the annuity at a %s%% discount rate. Taking the lump sum and investing it yourself may be the better choice if you are comfortable managing the money.',
                $this->money(abs($wealthGap)),
                $this->round($discountRatePercent, 2)
            );

        $sensitivity = [];
        foreach ([75, 85, 95] as $le) {
            $m = max(0, ($le - $currentAge) * 12);
            $npvAtLe = $this->growingAnnuityPv($monthlyPension, $r, $g, $m);
            $sensitivity["life_expectancy_{$le}"] = [
                'annuity_npv' => $this->round($npvAtLe),
                'wealth_gap' => $this->round($npvAtLe - $lumpSum),
                'better_option' => $npvAtLe > $lumpSum ? 'Monthly Annuity' : 'Lump Sum',
            ];
        }

        return [
            'results' => [
                'annuity_npv' => $this->round($npv),
                'lump_sum' => $this->round($lumpSum),
                'difference_annuity_minus_lump' => $this->round($wealthGap),
                'recommendation' => $recommendation,
                'breakeven_age' => $this->round($breakevenAge, 1),
                'years_of_payments' => $this->round($months / 12, 1),
            ],
            'breakdown' => [
                'monthly_discount_rate_percent' => $this->round($r * 100, 4),
                'monthly_cola_rate_percent' => $this->round($g * 100, 4),
                'total_months_of_payments' => (int) round($months),
                'life_expectancy_sensitivity' => $sensitivity,
                'formula' => 'PV = PMT × (1 − ((1+g)/(1+r))^n) / (r − g) for a growing ordinary annuity, where r and g are monthly discount and COLA rates and n is the number of monthly payments.',
            ],
            'units' => [
                'annuity_npv' => 'currency',
                'lump_sum' => 'currency',
                'difference_annuity_minus_lump' => 'currency',
                'breakeven_age' => 'years',
                'years_of_payments' => 'years',
            ],
        ];
    }

    protected function growingAnnuityPv(float $payment, float $r, float $g, float $months): float
    {
        if ($months <= 0) {
            return 0.0;
        }

        if (abs($r - $g) < 1e-9) {
            return $payment * $months / (1 + $r);
        }

        return $payment * (1 - ((1 + $g) / (1 + $r)) ** $months) / ($r - $g);
    }

    /**
     * ---------------------------------------------------------------
     * 2 & 3. Loan EMI / full amortization snapshot.
     * ---------------------------------------------------------------
     */
    protected function loanEmiEngine(array $inputs): array
    {
        [$emi, $months, $monthlyRate, $principal] = $this->computeEmi($inputs);

        $totalPayment = $emi * $months;
        $totalInterest = $totalPayment - $principal;

        return [
            'results' => [
                'monthly_emi' => $this->round($emi),
                'total_interest' => $this->round($totalInterest),
                'total_payment' => $this->round($totalPayment),
            ],
            'breakdown' => [
                'principal' => $this->round($principal),
                'tenure_months' => $months,
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
                'formula' => 'EMI = P × r × (1+r)^n ÷ ((1+r)^n − 1) (reducing balance)',
            ],
            'units' => [
                'monthly_emi' => 'currency',
                'total_interest' => 'currency',
                'total_payment' => 'currency',
            ],
        ];
    }

    protected function amortizationEngine(array $inputs): array
    {
        [$emi, $months, $monthlyRate, $principal] = $this->computeEmi($inputs);

        $totalPayment = $emi * $months;
        $totalInterest = $totalPayment - $principal;
        $firstMonthInterest = $principal * $monthlyRate;
        $firstMonthPrincipal = $emi - $firstMonthInterest;

        return [
            'results' => [
                'monthly_emi' => $this->round($emi),
                'total_interest' => $this->round($totalInterest),
                'total_payment' => $this->round($totalPayment),
                'first_month_principal' => $this->round($firstMonthPrincipal),
                'first_month_interest' => $this->round($firstMonthInterest),
            ],
            'breakdown' => [
                'principal' => $this->round($principal),
                'tenure_months' => $months,
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
                'formula' => 'EMI = P × r × (1+r)^n ÷ ((1+r)^n − 1); each month\'s interest = remaining balance × r.',
            ],
            'units' => [
                'monthly_emi' => 'currency',
                'total_interest' => 'currency',
                'total_payment' => 'currency',
                'first_month_principal' => 'currency',
                'first_month_interest' => 'currency',
            ],
        ];
    }

    /**
     * @return array{0: float, 1: int, 2: float, 3: float}
     */
    protected function computeEmi(array $inputs): array
    {
        $principal = $this->requireNumeric($inputs, 'loan_amount');
        $annualRate = $this->requireNumeric($inputs, 'annual_rate');
        $tenureYears = $this->requireNumeric($inputs, 'tenure_years');

        $months = max(1, (int) round($tenureYears * 12));
        $monthlyRate = $annualRate / 12 / 100;

        if ($monthlyRate == 0.0) {
            $emi = $this->safeDivide($principal, $months);
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $emi = $principal * $monthlyRate * $factor / ($factor - 1);
        }

        return [$emi, $months, $monthlyRate, $principal];
    }

    /**
     * ---------------------------------------------------------------
     * 4. APR -> APY.
     * ---------------------------------------------------------------
     */
    protected function aprToApyEngine(array $inputs): array
    {
        $aprPercent = $this->requireNumeric($inputs, 'apr_percent');
        $compoundsPerYear = max(1, $this->toFloat($inputs, 'compounds_per_year', 12));

        $apr = $aprPercent / 100;
        $apy = ((1 + $apr / $compoundsPerYear) ** $compoundsPerYear - 1) * 100;
        $effectiveGrowthOn10k = 10000 * ($apy / 100);

        return [
            'results' => [
                'apy_percent' => $this->round($apy, 4),
                'effective_growth' => $this->round($effectiveGrowthOn10k),
            ],
            'breakdown' => [
                'apr_percent' => $this->round($aprPercent, 4),
                'compounds_per_year' => (int) $compoundsPerYear,
                'formula' => 'APY = (1 + APR/n)^n − 1, where n is the number of compounding periods per year.',
                'note' => 'Effective growth shown is illustrative annual interest earned on a $10,000 balance at the computed APY.',
            ],
            'units' => [
                'apy_percent' => '%',
                'effective_growth' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 5. Compound savings / annuity accumulation.
     * ---------------------------------------------------------------
     */
    protected function compoundSavingsEngine(array $inputs): array
    {
        $start = $this->toFloat($inputs, 'starting_balance', 0);
        $contribution = $this->toFloat($inputs, 'monthly_contribution', 0);
        $annualReturn = $this->requireNumeric($inputs, 'annual_return');
        $years = $this->requireNumeric($inputs, 'years');

        $months = max(0, (int) round($years * 12));
        $monthlyRate = $annualReturn / 12 / 100;

        $fv = $start * (1 + $monthlyRate) ** $months;

        if ($monthlyRate == 0.0) {
            $fv += $contribution * $months;
        } else {
            $fv += $contribution * ((((1 + $monthlyRate) ** $months) - 1) / $monthlyRate);
        }

        $totalContributed = $start + $contribution * $months;
        $growth = $fv - $totalContributed;

        return [
            'results' => [
                'future_value' => $this->round($fv),
                'total_contributed' => $this->round($totalContributed),
                'growth' => $this->round($growth),
            ],
            'breakdown' => [
                'months' => $months,
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
                'formula' => 'FV = Start×(1+r)^n + Contribution×(((1+r)^n − 1) / r)',
            ],
            'units' => [
                'future_value' => 'currency',
                'total_contributed' => 'currency',
                'growth' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 6. Simple ROI.
     * ---------------------------------------------------------------
     */
    protected function simpleRoiEngine(array $inputs): array
    {
        $initial = $this->requireNumeric($inputs, 'initial_value');
        $final = $this->requireNumeric($inputs, 'final_value');
        $years = $this->toFloat($inputs, 'years', 0);

        $netGain = $final - $initial;
        $roiPercent = $this->percentageOf($netGain, $initial);

        $annualizedRoiPercent = $roiPercent;
        if ($years > 0 && $initial > 0 && $final > 0) {
            $annualizedRoiPercent = ((($final / $initial) ** (1 / $years)) - 1) * 100;
        }

        return [
            'results' => [
                'net_gain' => $this->round($netGain),
                'roi_percent' => $this->round($roiPercent, 2),
                'annualized_roi_percent' => $this->round($annualizedRoiPercent, 2),
            ],
            'breakdown' => [
                'initial_value' => $this->round($initial),
                'final_value' => $this->round($final),
                'years' => $this->round($years, 2),
                'formula' => 'ROI% = (Final − Initial) / Initial × 100; Annualized = ((Final/Initial)^(1/years) − 1) × 100',
            ],
            'units' => [
                'net_gain' => 'currency',
                'roi_percent' => '%',
                'annualized_roi_percent' => '%',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 7. Net worth.
     * ---------------------------------------------------------------
     */
    protected function netWorthEngine(array $inputs): array
    {
        $cash = $this->toFloat($inputs, 'cash', 0);
        $investments = $this->toFloat($inputs, 'investments', 0);
        $property = $this->toFloat($inputs, 'property', 0);
        $otherAssets = $this->toFloat($inputs, 'other_assets', 0);
        $mortgage = $this->toFloat($inputs, 'mortgage', 0);
        $otherDebts = $this->toFloat($inputs, 'other_debts', 0);

        $totalAssets = $cash + $investments + $property + $otherAssets;
        $totalLiabilities = $mortgage + $otherDebts;
        $netWorth = $totalAssets - $totalLiabilities;

        return [
            'results' => [
                'total_assets' => $this->round($totalAssets),
                'total_liabilities' => $this->round($totalLiabilities),
                'net_worth' => $this->round($netWorth),
            ],
            'breakdown' => [
                'cash' => $this->round($cash),
                'investments' => $this->round($investments),
                'property' => $this->round($property),
                'other_assets' => $this->round($otherAssets),
                'mortgage' => $this->round($mortgage),
                'other_debts' => $this->round($otherDebts),
                'formula' => 'Net Worth = (Cash + Investments + Property + Other Assets) − (Mortgage + Other Debts)',
            ],
            'units' => [
                'total_assets' => 'currency',
                'total_liabilities' => 'currency',
                'net_worth' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 8. 50/30/20 budget rule.
     * ---------------------------------------------------------------
     */
    protected function budget503020Engine(array $inputs): array
    {
        $income = $this->requireNumeric($inputs, 'monthly_income');
        $needs = $this->toFloat($inputs, 'needs', 0);
        $wants = $this->toFloat($inputs, 'wants', 0);
        $savings = $this->toFloat($inputs, 'savings', 0);

        $needsPct = $this->percentageOf($needs, $income);
        $wantsPct = $this->percentageOf($wants, $income);
        $savingsPct = $this->percentageOf($savings, $income);

        return [
            'results' => [
                'needs_pct' => $this->round($needsPct, 1),
                'wants_pct' => $this->round($wantsPct, 1),
                'savings_pct' => $this->round($savingsPct, 1),
                'ideal_needs' => $this->round($income * 0.5),
                'ideal_wants' => $this->round($income * 0.3),
                'ideal_savings' => $this->round($income * 0.2),
                'variance_needs' => $this->round($needsPct - 50, 1),
            ],
            'breakdown' => [
                'monthly_income' => $this->round($income),
                'formula' => 'Ideal split: 50% needs, 30% wants, 20% savings/debt payoff of take-home income.',
            ],
            'units' => [
                'needs_pct' => '%',
                'wants_pct' => '%',
                'savings_pct' => '%',
                'ideal_needs' => 'currency',
                'ideal_wants' => 'currency',
                'ideal_savings' => 'currency',
                'variance_needs' => '%',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 9. Generic two-cost comparison.
     * ---------------------------------------------------------------
     */
    protected function twoCostCompareEngine(array $inputs): array
    {
        $costA = $this->requireNumeric($inputs, 'cost_a');
        $costB = $this->requireNumeric($inputs, 'cost_b');

        $labelA = $this->definition['meta']['option_a_label'] ?? 'Option A';
        $labelB = $this->definition['meta']['option_b_label'] ?? 'Option B';

        $difference = abs($costA - $costB);
        $cheaperOption = match (true) {
            $costA < $costB => $labelA,
            $costB < $costA => $labelB,
            default => 'Tie',
        };

        return [
            'results' => [
                'cost_a' => $this->round($costA),
                'cost_b' => $this->round($costB),
                'difference' => $this->round($difference),
                'cheaper_option' => $cheaperOption,
            ],
            'breakdown' => [
                'option_a_label' => $labelA,
                'option_b_label' => $labelB,
                'formula' => 'Difference = |Cost A − Cost B|; cheaper option is whichever total cost is lower.',
            ],
            'units' => [
                'cost_a' => 'currency',
                'cost_b' => 'currency',
                'difference' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 10. Income-multiple coverage need (life / disability insurance).
     * ---------------------------------------------------------------
     */
    protected function incomeMultipleNeedEngine(array $inputs): array
    {
        $annualIncome = $this->requireNumeric($inputs, 'annual_income');
        $yearsIncome = $this->requireNumeric($inputs, 'years_income');
        $debts = $this->toFloat($inputs, 'debts', 0);
        $existingCover = $this->toFloat($inputs, 'existing_cover', 0);

        $recommendedCover = $annualIncome * $yearsIncome + $debts;
        $gap = max(0, $recommendedCover - $existingCover);

        return [
            'results' => [
                'recommended_cover' => $this->round($recommendedCover),
                'gap' => $this->round($gap),
            ],
            'breakdown' => [
                'annual_income' => $this->round($annualIncome),
                'years_income' => $this->round($yearsIncome, 1),
                'debts' => $this->round($debts),
                'existing_cover' => $this->round($existingCover),
                'formula' => 'Recommended Cover = Annual Income × Years of Income Replacement + Debts; Gap = Recommended − Existing Cover.',
            ],
            'units' => [
                'recommended_cover' => 'currency',
                'gap' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 11. Insurance premium vs expected out-of-pocket / self-insure.
     * ---------------------------------------------------------------
     */
    protected function premiumVsExpectedEngine(array $inputs): array
    {
        $annualPremium = $this->requireNumeric($inputs, 'annual_premium');
        $years = $this->requireNumeric($inputs, 'years');
        $expectedAnnualClaims = $this->requireNumeric($inputs, 'expected_annual_claims');
        $deductibleAnnual = $this->toFloat($inputs, 'deductible_annual', 0);

        $totalPremiums = $annualPremium * $years;
        $expectedOutOfPocketInsured = $totalPremiums + min($deductibleAnnual, $expectedAnnualClaims) * $years;
        $selfInsureCost = $expectedAnnualClaims * $years;

        $recommendation = $expectedOutOfPocketInsured <= $selfInsureCost
            ? 'Buying insurance looks cheaper than self-insuring, based on your expected claims.'
            : 'Self-insuring (skipping coverage and saving the premium) looks cheaper, based on your expected claims — but insurance also protects against rare, larger-than-expected losses.';

        return [
            'results' => [
                'total_premiums' => $this->round($totalPremiums),
                'expected_out_of_pocket_insured' => $this->round($expectedOutOfPocketInsured),
                'self_insure_cost' => $this->round($selfInsureCost),
                'recommendation' => $recommendation,
            ],
            'breakdown' => [
                'annual_premium' => $this->round($annualPremium),
                'years' => $this->round($years, 1),
                'expected_annual_claims' => $this->round($expectedAnnualClaims),
                'deductible_annual' => $this->round($deductibleAnnual),
                'formula' => 'Insured total = Premiums + min(Deductible, Expected Claims) × Years; Self-insure total = Expected Claims × Years.',
            ],
            'units' => [
                'total_premiums' => 'currency',
                'expected_out_of_pocket_insured' => 'currency',
                'self_insure_cost' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 12. Generic linear unit conversion (fixed or dynamic rate).
     * ---------------------------------------------------------------
     */
    protected function unitConvertEngine(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');

        $factor = isset($inputs['rate']) && is_numeric($inputs['rate'])
            ? (float) $inputs['rate']
            : (float) ($this->definition['factor'] ?? 1.0);

        $converted = $value * $factor;

        return [
            'results' => [
                'converted_value' => $this->round($converted, 6),
            ],
            'breakdown' => [
                'input_value' => $this->round($value, 6),
                'factor_used' => $factor,
                'formula' => 'Converted Value = Value × Factor',
            ],
            'units' => [
                'converted_value' => $this->definition['meta']['result_unit'] ?? '',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 13. Paycheck / take-home pay estimator.
     * ---------------------------------------------------------------
     */
    protected function paycheckEngine(array $inputs): array
    {
        $gross = $this->requireNumeric($inputs, 'gross_pay');
        $periods = $this->toFloat($inputs, 'pay_periods_per_year', $this->definition['pay_periods_per_year'] ?? 26);
        $periods = $periods > 0 ? $periods : 26;

        $federalRatePercent = $this->toFloat($inputs, 'federal_rate', 12);
        $defaultStateRate = ($this->definition['state_rate'] ?? 0) * 100;
        $stateRatePercent = $this->toFloat($inputs, 'state_rate', $defaultStateRate);
        $ficaRatePercent = $this->toFloat($inputs, 'fica_rate', 7.65);

        $grossAnnual = $gross * $periods;
        $federalTax = $grossAnnual * $federalRatePercent / 100;
        $stateTax = $grossAnnual * $stateRatePercent / 100;
        $fica = $grossAnnual * $ficaRatePercent / 100;
        $netAnnual = $grossAnnual - $federalTax - $stateTax - $fica;
        $netPerPaycheck = $this->safeDivide($netAnnual, $periods);

        $stateName = $this->definition['meta']['state_name'] ?? null;

        return [
            'results' => [
                'gross_annual' => $this->round($grossAnnual),
                'federal_tax' => $this->round($federalTax),
                'state_tax' => $this->round($stateTax),
                'fica' => $this->round($fica),
                'net_per_paycheck' => $this->round($netPerPaycheck),
                'net_annual' => $this->round($netAnnual),
            ],
            'breakdown' => [
                'pay_periods_per_year' => (int) $periods,
                'federal_rate_percent' => $this->round($federalRatePercent, 2),
                'state_rate_percent' => $this->round($stateRatePercent, 2),
                'fica_rate_percent' => $this->round($ficaRatePercent, 2),
                'state' => $stateName,
                'note' => 'Educational estimate using flat approximate tax rates, not official payroll withholding tables. FICA covers Social Security + Medicare (7.65% combined employee share).',
            ],
            'units' => [
                'gross_annual' => 'currency',
                'federal_tax' => 'currency',
                'state_tax' => 'currency',
                'fica' => 'currency',
                'net_per_paycheck' => 'currency',
                'net_annual' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 14. Credit card payoff time / interest.
     * ---------------------------------------------------------------
     */
    protected function creditCardPayoffEngine(array $inputs): array
    {
        $balance = $this->requireNumeric($inputs, 'balance');
        $apr = $this->requireNumeric($inputs, 'apr');
        $payment = $this->requireNumeric($inputs, 'monthly_payment');

        $monthlyRate = $apr / 12 / 100;
        $bal = $balance;
        $totalInterest = 0.0;
        $months = 0;
        $neverPaysOff = $monthlyRate > 0 && $payment <= $bal * $monthlyRate;

        if (! $neverPaysOff) {
            while ($bal > 0.01 && $months < 600) {
                $interest = $bal * $monthlyRate;
                $principalPaid = $payment - $interest;

                if ($principalPaid <= 0) {
                    $neverPaysOff = true;
                    break;
                }

                $principalPaid = min($principalPaid, $bal);
                $bal -= $principalPaid;
                $totalInterest += $interest;
                $months++;
            }
        }

        if ($neverPaysOff) {
            $months = 600;
        }

        $totalPaid = $balance + $totalInterest;

        return [
            'results' => [
                'months_to_payoff' => $neverPaysOff ? null : $months,
                'total_interest' => $this->round($totalInterest),
                'total_paid' => $this->round($totalPaid),
            ],
            'breakdown' => [
                'balance' => $this->round($balance),
                'monthly_rate_percent' => $this->round($monthlyRate * 100, 4),
                'warning' => $neverPaysOff
                    ? 'Your monthly payment does not cover the monthly interest — the balance will never be paid off at this payment amount. Increase your payment.'
                    : null,
                'formula' => 'Each month: Interest = Balance × (APR/12); Principal Paid = Payment − Interest; Balance decreases by Principal Paid.',
            ],
            'units' => [
                'months_to_payoff' => 'months',
                'total_interest' => 'currency',
                'total_paid' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 15. Can-I-afford-it verdict.
     * ---------------------------------------------------------------
     */
    protected function affordabilityEngine(array $inputs): array
    {
        $price = $this->requireNumeric($inputs, 'item_price');
        $income = $this->requireNumeric($inputs, 'monthly_income');
        $expenses = $this->toFloat($inputs, 'monthly_expenses', 0);
        $savings = $this->toFloat($inputs, 'savings', 0);

        $leftover = $income - $expenses;
        $monthsToSave = $leftover > 0 ? $this->safeDivide($price, $leftover) : null;

        $verdict = 'NO';
        if ($leftover > 0 && ($leftover * 0.2 * 12 > $price / 5 || ($savings >= $price * 0.2 && $leftover > 0))) {
            $verdict = 'YES';
        } elseif ($leftover > 0 || $savings > 0) {
            $verdict = 'MAYBE';
        }

        return [
            'results' => [
                'leftover' => $this->round($leftover),
                'months_to_save' => $monthsToSave === null ? null : $this->round($monthsToSave, 1),
                'verdict' => $verdict,
            ],
            'breakdown' => [
                'item_price' => $this->round($price),
                'monthly_income' => $this->round($income),
                'monthly_expenses' => $this->round($expenses),
                'savings' => $this->round($savings),
                'formula' => 'Leftover = Income − Expenses. YES if leftover comfortably covers the price within ~5 years or savings already cover 20% of the price with positive cash flow.',
            ],
            'units' => [
                'leftover' => 'currency',
                'months_to_save' => 'months',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 16. Subscription waste audit.
     * ---------------------------------------------------------------
     */
    protected function subscriptionAuditEngine(array $inputs): array
    {
        $monthlySubscriptions = $this->requireNumeric($inputs, 'monthly_subscriptions');
        $unusedCount = $this->toFloat($inputs, 'unused_count', 0);
        $avgUnusedCost = $this->toFloat($inputs, 'avg_unused_cost', 0);

        $annualCost = $monthlySubscriptions * 12;
        $estimatedWasteAnnual = $unusedCount * $avgUnusedCost * 12;
        $keepBudget = max(0, $annualCost - $estimatedWasteAnnual);

        return [
            'results' => [
                'annual_cost' => $this->round($annualCost),
                'estimated_waste_annual' => $this->round($estimatedWasteAnnual),
                'keep_budget' => $this->round($keepBudget),
            ],
            'breakdown' => [
                'monthly_subscriptions' => $this->round($monthlySubscriptions),
                'unused_count' => $unusedCount,
                'avg_unused_cost' => $this->round($avgUnusedCost),
                'formula' => 'Annual Cost = Monthly Total × 12; Waste = Unused Count × Avg Unused Cost × 12.',
            ],
            'units' => [
                'annual_cost' => 'currency',
                'estimated_waste_annual' => 'currency',
                'keep_budget' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 17. Lifetime nominal + inflation-adjusted cost projection.
     * ---------------------------------------------------------------
     */
    protected function lifetimeCostEngine(array $inputs): array
    {
        $annualCost = $this->requireNumeric($inputs, 'annual_cost');
        $years = $this->requireNumeric($inputs, 'years');
        $inflationPercent = $this->toFloat($inputs, 'inflation_percent', 0);

        $totalNominal = $annualCost * $years;

        $totalInflated = 0.0;
        $inflationRate = $inflationPercent / 100;
        $wholeYears = max(0, (int) round($years));

        for ($y = 0; $y < $wholeYears; $y++) {
            $totalInflated += $annualCost * (1 + $inflationRate) ** $y;
        }

        return [
            'results' => [
                'total_nominal' => $this->round($totalNominal),
                'total_inflated' => $this->round($totalInflated),
            ],
            'breakdown' => [
                'annual_cost' => $this->round($annualCost),
                'years' => $wholeYears,
                'inflation_percent' => $this->round($inflationPercent, 2),
                'formula' => 'Nominal Total = Annual Cost × Years; Inflated Total = Σ Annual Cost × (1+inflation)^year for each year.',
            ],
            'units' => [
                'total_nominal' => 'currency',
                'total_inflated' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 18. Days between two dates.
     * ---------------------------------------------------------------
     */
    protected function daysBetweenEngine(array $inputs): array
    {
        $start = $this->parseDate($this->toString($inputs, 'start_date'));
        $end = $this->parseDate($this->toString($inputs, 'end_date'));

        $days = abs($end->diffInDays($start));
        $weeks = $days / 7;
        $monthsApprox = $days / 30.4368;

        return [
            'results' => [
                'days' => $days,
                'weeks' => $this->round($weeks, 2),
                'months_approx' => $this->round($monthsApprox, 2),
            ],
            'breakdown' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'formula' => 'Days = |End Date − Start Date|; Weeks = Days ÷ 7; Months ≈ Days ÷ 30.44.',
            ],
            'units' => [
                'days' => 'days',
                'weeks' => 'weeks',
                'months_approx' => 'months',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 19. Add/subtract days from a date.
     * ---------------------------------------------------------------
     */
    protected function dateOffsetEngine(array $inputs): array
    {
        $start = $this->parseDate($this->toString($inputs, 'start_date'));
        $days = (int) round($this->requireNumeric($inputs, 'days'));

        $result = $start->copy()->addDays($days);

        return [
            'results' => [
                'result_date' => $result->toDateString(),
            ],
            'breakdown' => [
                'start_date' => $start->toDateString(),
                'days_offset' => $days,
                'result_day_of_week' => $result->format('l'),
                'formula' => 'Result Date = Start Date + Days (negative days subtract).',
            ],
            'units' => [],
        ];
    }

    protected function parseDate(string $value): Carbon
    {
        try {
            return Carbon::parse($value !== '' ? $value : 'now');
        } catch (\Throwable) {
            return Carbon::now();
        }
    }

    /**
     * ---------------------------------------------------------------
     * 20. Average / mean of a comma-separated list.
     * ---------------------------------------------------------------
     */
    protected function averageMeanEngine(array $inputs): array
    {
        $raw = $this->toString($inputs, 'values', '');
        $values = array_values(array_filter(
            array_map(static fn ($v) => trim($v), explode(',', $raw)),
            static fn ($v) => $v !== '' && is_numeric($v)
        ));
        $numbers = array_map('floatval', $values);

        $count = count($numbers);
        $sum = array_sum($numbers);
        $mean = $this->safeDivide($sum, $count);

        return [
            'results' => [
                'count' => $count,
                'sum' => $this->round($sum, 4),
                'mean' => $this->round($mean, 4),
            ],
            'breakdown' => [
                'formula' => 'Mean = Sum of values ÷ Count of values.',
            ],
            'units' => [],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 21. Rectangle area / perimeter.
     * ---------------------------------------------------------------
     */
    protected function areaRectEngine(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $width = $this->requireNumeric($inputs, 'width');

        return [
            'results' => [
                'area' => $this->round($length * $width, 4),
                'perimeter' => $this->round(2 * ($length + $width), 4),
            ],
            'breakdown' => [
                'formula' => 'Area = Length × Width; Perimeter = 2 × (Length + Width).',
            ],
            'units' => [
                'area' => 'sq units',
                'perimeter' => 'units',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 22. Box volume / surface area.
     * ---------------------------------------------------------------
     */
    protected function volumeBoxEngine(array $inputs): array
    {
        $length = $this->requireNumeric($inputs, 'length');
        $width = $this->requireNumeric($inputs, 'width');
        $height = $this->requireNumeric($inputs, 'height');

        $volume = $length * $width * $height;
        $surfaceArea = 2 * ($length * $width + $length * $height + $width * $height);

        return [
            'results' => [
                'volume' => $this->round($volume, 4),
                'surface_area' => $this->round($surfaceArea, 4),
            ],
            'breakdown' => [
                'formula' => 'Volume = L × W × H; Surface Area = 2 × (LW + LH + WH).',
            ],
            'units' => [
                'volume' => 'cubic units',
                'surface_area' => 'sq units',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 23. Aspect ratio simplification.
     * ---------------------------------------------------------------
     */
    protected function aspectRatioEngine(array $inputs): array
    {
        $width = $this->requireNumeric($inputs, 'width');
        $height = $this->requireNumeric($inputs, 'height');

        $w = max(1, (int) round($width));
        $h = max(1, (int) round($height));
        $divisor = $this->gcd($w, $h);

        $ratioText = ($w / $divisor).':'.($h / $divisor);
        $decimal = $this->safeDivide($width, $height);

        return [
            'results' => [
                'ratio_text' => $ratioText,
                'decimal' => $this->round($decimal, 4),
            ],
            'breakdown' => [
                'gcd' => $divisor,
                'formula' => 'Ratio simplified by dividing both sides by their greatest common divisor (GCD).',
            ],
            'units' => [],
        ];
    }

    protected function gcd(int $a, int $b): int
    {
        $a = abs($a);
        $b = abs($b);

        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }

        return $a === 0 ? 1 : $a;
    }

    /**
     * ---------------------------------------------------------------
     * 24. Celsius <-> Fahrenheit temperature conversion.
     * ---------------------------------------------------------------
     */
    protected function tempConvertEngine(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $direction = $this->toString($inputs, 'direction', $this->definition['direction'] ?? 'c_to_f');

        if ($direction === 'f_to_c') {
            $converted = ($value - 32) * 5 / 9;
            $formula = '°C = (°F − 32) × 5/9';
        } else {
            $converted = $value * 9 / 5 + 32;
            $formula = '°F = °C × 9/5 + 32';
        }

        return [
            'results' => [
                'converted_value' => $this->round($converted, 2),
            ],
            'breakdown' => [
                'direction' => $direction,
                'formula' => $formula,
            ],
            'units' => [
                'converted_value' => $direction === 'f_to_c' ? '°C' : '°F',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 25. Word / character / sentence counter.
     * ---------------------------------------------------------------
     */
    protected function wordCounterEngine(array $inputs): array
    {
        $text = $this->toString($inputs, 'text', '');

        $characters = mb_strlen($text);
        $charactersNoSpaces = mb_strlen(str_replace([' ', "\t", "\n", "\r"], '', $text));
        $trimmed = trim($text);
        $words = $trimmed === '' ? 0 : count(array_filter(preg_split('/\s+/u', $trimmed) ?: []));
        $sentenceParts = array_filter(preg_split('/[.!?]+/u', $text) ?: [], static fn ($p) => trim($p) !== '');
        $sentences = count($sentenceParts);

        return [
            'results' => [
                'characters' => $characters,
                'characters_no_spaces' => $charactersNoSpaces,
                'words' => $words,
                'sentences' => $sentences,
            ],
            'breakdown' => [
                'formula' => 'Words are split on whitespace; sentences are split on ./!/? punctuation.',
            ],
            'units' => [],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 26. FIRE Monte-Carlo style success probability (simplified).
     * ---------------------------------------------------------------
     */
    protected function fireSuccessEngine(array $inputs): array
    {
        $portfolio = $this->requireNumeric($inputs, 'portfolio');
        $annualSpend = $this->requireNumeric($inputs, 'annual_spend');
        $years = max(1, (int) round($this->requireNumeric($inputs, 'years')));
        $meanReturn = $this->toFloat($inputs, 'mean_return', 7) / 100;
        $volatility = max(0.0001, $this->toFloat($inputs, 'volatility', 15) / 100);

        $seed = crc32(json_encode([$portfolio, $annualSpend, $years, $meanReturn, $volatility]));
        mt_srand($seed);

        $paths = 200;
        $successCount = 0;
        $endings = [];

        for ($p = 0; $p < $paths; $p++) {
            $balance = $portfolio;

            for ($y = 0; $y < $years; $y++) {
                $z = $this->randomStandardNormal();
                $return = $meanReturn + $volatility * $z;
                $balance = $balance * (1 + $return) - $annualSpend;

                if ($balance <= 0) {
                    $balance = 0;
                    break;
                }
            }

            $endings[] = $balance;

            if ($balance > 0) {
                $successCount++;
            }
        }

        sort($endings);
        $medianEnding = $endings[(int) floor(count($endings) / 2)] ?? 0;
        $successProbabilityPercent = $this->percentageOf($successCount, $paths);

        return [
            'results' => [
                'success_probability_percent' => $this->round($successProbabilityPercent, 1),
                'median_ending' => $this->round($medianEnding),
            ],
            'breakdown' => [
                'simulated_paths' => $paths,
                'years' => $years,
                'mean_return_percent' => $this->round($meanReturn * 100, 2),
                'volatility_percent' => $this->round($volatility * 100, 2),
                'formula' => 'Simplified Monte Carlo: 200 simulated paths using a normal-distributed annual return (mean, volatility); success = portfolio stays above $0 through the full horizon after withdrawals.',
            ],
            'units' => [
                'success_probability_percent' => '%',
                'median_ending' => 'currency',
            ],
        ];
    }

    protected function randomStandardNormal(): float
    {
        $u1 = max(1e-9, mt_rand() / mt_getrandmax());
        $u2 = mt_rand() / mt_getrandmax();

        return sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
    }

    /**
     * ---------------------------------------------------------------
     * 27. PMI removal date (80% / 78% LTV).
     * ---------------------------------------------------------------
     */
    protected function pmiRemovalEngine(array $inputs): array
    {
        $homeValue = $this->requireNumeric($inputs, 'home_value');
        $loanBalance = $this->requireNumeric($inputs, 'loan_balance');
        $extraMonthly = $this->toFloat($inputs, 'extra_monthly', 0);
        $annualRate = $this->requireNumeric($inputs, 'annual_rate');
        $originalTermYears = $this->requireNumeric($inputs, 'original_term_years');

        $ltvPercent = $this->percentageOf($loanBalance, $homeValue);
        $monthlyRate = $annualRate / 12 / 100;

        $months = max(1, (int) round($originalTermYears * 12));
        if ($monthlyRate == 0.0) {
            $payment = $this->safeDivide($loanBalance, $months);
        } else {
            $factor = (1 + $monthlyRate) ** $months;
            $payment = $loanBalance * $monthlyRate * $factor / ($factor - 1);
        }

        $balance = $loanBalance;
        $monthsTo80 = null;
        $monthsTo78 = null;

        for ($m = 1; $m <= 600; $m++) {
            $interest = $balance * $monthlyRate;
            $principal = ($payment - $interest) + $extraMonthly;
            $balance = max(0, $balance - $principal);
            $ltv = $this->percentageOf($balance, $homeValue);

            if ($monthsTo80 === null && $ltv <= 80) {
                $monthsTo80 = $m;
            }

            if ($monthsTo78 === null && $ltv <= 78) {
                $monthsTo78 = $m;
                break;
            }

            if ($balance <= 0) {
                break;
            }
        }

        return [
            'results' => [
                'ltv_percent' => $this->round($ltvPercent, 2),
                'months_to_80' => $monthsTo80,
                'months_to_78' => $monthsTo78,
            ],
            'breakdown' => [
                'estimated_monthly_payment' => $this->round($payment),
                'formula' => 'LTV = Loan Balance ÷ Home Value × 100. Amortization is simulated month by month (plus any extra payment) until the balance falls to 80% and 78% of home value — the federal PMI cancellation thresholds.',
            ],
            'units' => [
                'ltv_percent' => '%',
                'months_to_80' => 'months',
                'months_to_78' => 'months',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 28. Social Security claiming-age breakeven.
     * ---------------------------------------------------------------
     */
    protected function ssBreakevenEngine(array $inputs): array
    {
        $at62 = $this->requireNumeric($inputs, 'monthly_at_62');
        $at67 = $this->requireNumeric($inputs, 'monthly_at_67');
        $at70 = $this->requireNumeric($inputs, 'monthly_at_70');

        $breakeven67vs62 = $this->breakevenAge($at62, 62, $at67, 67);
        $breakeven70vs67 = $this->breakevenAge($at67, 67, $at70, 70);

        return [
            'results' => [
                'breakeven_67_vs_62_age' => $breakeven67vs62 === null ? null : $this->round($breakeven67vs62, 1),
                'breakeven_70_vs_67_age' => $breakeven70vs67 === null ? null : $this->round($breakeven70vs67, 1),
            ],
            'breakdown' => [
                'monthly_at_62' => $this->round($at62),
                'monthly_at_67' => $this->round($at67),
                'monthly_at_70' => $this->round($at70),
                'formula' => 'Breakeven age is where cumulative lifetime benefits from claiming later catch up with cumulative benefits from claiming earlier: EarlyAmount×12×(Age−EarlyStart) = LateAmount×12×(Age−LateStart).',
            ],
            'units' => [
                'breakeven_67_vs_62_age' => 'years',
                'breakeven_70_vs_67_age' => 'years',
            ],
        ];
    }

    protected function breakevenAge(float $earlyAmount, float $earlyStart, float $lateAmount, float $lateStart): ?float
    {
        if (abs($earlyAmount - $lateAmount) < 1e-9) {
            return null;
        }

        $age = ($earlyStart * $earlyAmount - $lateStart * $lateAmount) / ($earlyAmount - $lateAmount);

        return $age > $lateStart ? $age : null;
    }

    /**
     * ---------------------------------------------------------------
     * 29. Required Minimum Distribution (RMD).
     * ---------------------------------------------------------------
     */
    protected function rmdEngine(array $inputs): array
    {
        $accountBalance = $this->requireNumeric($inputs, 'account_balance');
        $age = (int) round($this->requireNumeric($inputs, 'age'));

        $table = [
            73 => 26.5, 74 => 25.5, 75 => 24.6, 76 => 23.7, 77 => 22.9,
            78 => 22.0, 79 => 21.1, 80 => 20.2, 81 => 19.4, 82 => 18.5,
            83 => 17.7, 84 => 16.8, 85 => 16.0, 86 => 15.2, 87 => 14.4,
            88 => 13.7, 89 => 12.9, 90 => 12.2,
        ];

        $factor = $table[$age] ?? 27.4;
        $rmdAmount = $this->safeDivide($accountBalance, $factor);

        return [
            'results' => [
                'rmd_amount' => $this->round($rmdAmount),
            ],
            'breakdown' => [
                'age' => $age,
                'irs_uniform_lifetime_factor' => $factor,
                'formula' => 'RMD = Account Balance (as of Dec 31 prior year) ÷ IRS Uniform Lifetime Table distribution factor for your age.',
            ],
            'units' => [
                'rmd_amount' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 30. A1c estimate from average glucose.
     * ---------------------------------------------------------------
     */
    protected function a1cEngine(array $inputs): array
    {
        $avgGlucose = $this->requireNumeric($inputs, 'average_glucose_mgdl');
        $estimatedA1c = ($avgGlucose + 46.7) / 28.7;

        return [
            'results' => [
                'estimated_a1c' => $this->round($estimatedA1c, 2),
            ],
            'breakdown' => [
                'formula' => 'Estimated A1c = (Average Glucose mg/dL + 46.7) ÷ 28.7 (ADAG study formula).',
            ],
            'units' => [
                'estimated_a1c' => '%',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 31. Blood alcohol content (Widmark formula).
     * ---------------------------------------------------------------
     */
    protected function bacEngine(array $inputs): array
    {
        $drinks = $this->requireNumeric($inputs, 'drinks');
        $drinkAlcoholOz = $this->toFloat($inputs, 'drink_alcohol_oz', 0.6);
        $weightLb = $this->requireNumeric($inputs, 'weight_lb');
        $sex = $this->toString($inputs, 'sex', 'male');
        $hours = $this->toFloat($inputs, 'hours', 0);

        $r = $sex === 'female' ? 0.55 : 0.68;

        $alcoholGrams = $drinks * $drinkAlcoholOz * 29.5735 * 0.789;
        $bodyWeightGrams = $weightLb * 453.592;

        $bac = $this->safeDivide($alcoholGrams, $bodyWeightGrams * $r) * 100 - 0.015 * $hours;
        $bac = max(0, $bac);

        return [
            'results' => [
                'bac' => $this->round($bac, 3),
            ],
            'breakdown' => [
                'widmark_constant_r' => $r,
                'alcohol_grams' => $this->round($alcoholGrams, 2),
                'formula' => 'BAC = (Alcohol grams ÷ (Body weight grams × r)) × 100 − (0.015 × hours since first drink), Widmark formula.',
                'disclaimer' => 'Educational estimate only. Never drive after drinking — actual BAC varies by metabolism, food intake and other factors.',
            ],
            'units' => [
                'bac' => '%',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 32. Macro split from calories + percentages.
     * ---------------------------------------------------------------
     */
    protected function macrosEngine(array $inputs): array
    {
        $calories = $this->requireNumeric($inputs, 'calories');
        $proteinPct = $this->toFloat($inputs, 'protein_pct', 30);
        $carbPct = $this->toFloat($inputs, 'carb_pct', 40);
        $fatPct = $this->toFloat($inputs, 'fat_pct', 30);

        $proteinG = $calories * $proteinPct / 100 / 4;
        $carbG = $calories * $carbPct / 100 / 4;
        $fatG = $calories * $fatPct / 100 / 9;

        return [
            'results' => [
                'protein_g' => $this->round($proteinG),
                'carb_g' => $this->round($carbG),
                'fat_g' => $this->round($fatG),
            ],
            'breakdown' => [
                'calories' => $this->round($calories),
                'split_percent' => "{$proteinPct}P / {$carbPct}C / {$fatPct}F",
                'formula' => 'Protein/Carb grams = Calories × %/100 ÷ 4 kcal/g; Fat grams = Calories × %/100 ÷ 9 kcal/g.',
            ],
            'units' => [
                'protein_g' => 'g',
                'carb_g' => 'g',
                'fat_g' => 'g',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 33. Sleep cycle bedtime suggestions.
     * ---------------------------------------------------------------
     */
    protected function sleepCyclesEngine(array $inputs): array
    {
        $wakeTimeRaw = $this->toString($inputs, 'wake_time', '07:00');

        try {
            $wake = Carbon::createFromFormat('H:i', $wakeTimeRaw) ?: Carbon::now();
        } catch (\Throwable) {
            $wake = Carbon::now();
        }

        $fallAsleepMinutes = 15;
        $bedtime5 = $wake->copy()->subMinutes(5 * 90 + $fallAsleepMinutes);
        $bedtime6 = $wake->copy()->subMinutes(6 * 90 + $fallAsleepMinutes);

        return [
            'results' => [
                'bedtime_5_cycles' => $bedtime5->format('H:i'),
                'bedtime_6_cycles' => $bedtime6->format('H:i'),
            ],
            'breakdown' => [
                'wake_time' => $wake->format('H:i'),
                'cycle_length_minutes' => 90,
                'fall_asleep_buffer_minutes' => $fallAsleepMinutes,
                'formula' => 'Bedtime = Wake Time − (Cycles × 90 min) − 15 min to fall asleep. Each sleep cycle is ~90 minutes.',
            ],
            'units' => [],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 34. Dog age -> human years.
     * ---------------------------------------------------------------
     */
    protected function dogHumanAgeEngine(array $inputs): array
    {
        $dogAgeYears = $this->requireNumeric($inputs, 'dog_age_years');
        $size = $this->toString($inputs, 'size', 'medium');

        if ($dogAgeYears <= 0) {
            $humanYears = 0.0;
        } elseif ($dogAgeYears < 1) {
            $humanYears = 31 * $dogAgeYears;
        } else {
            $humanYears = 16 * log($dogAgeYears) + 31;
        }

        $sizeAdjustment = [
            'small' => -2.0,
            'medium' => 0.0,
            'large' => 2.0,
            'giant' => 4.0,
        ][$size] ?? 0.0;

        if ($dogAgeYears > 5) {
            $humanYears += $sizeAdjustment;
        }

        $humanYears = max(0, $humanYears);

        return [
            'results' => [
                'human_years' => $this->round($humanYears, 1),
            ],
            'breakdown' => [
                'size_category' => $size,
                'formula' => 'Human Age ≈ 16 × ln(Dog Age) + 31 (AVMA-style logarithmic formula), with a small size adjustment for dogs over 5.',
            ],
            'units' => [
                'human_years' => 'years',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 35. LLM token cost.
     * ---------------------------------------------------------------
     */
    protected function tokenCostEngine(array $inputs): array
    {
        $tokens = $this->requireNumeric($inputs, 'tokens');
        $pricePerMillion = $this->requireNumeric($inputs, 'price_per_million');

        $cost = $tokens / 1000000 * $pricePerMillion;

        return [
            'results' => [
                'cost' => $this->round($cost, 4),
            ],
            'breakdown' => [
                'formula' => 'Cost = (Tokens ÷ 1,000,000) × Price per Million Tokens.',
            ],
            'units' => [
                'cost' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 36. Meeting cost.
     * ---------------------------------------------------------------
     */
    protected function meetingCostEngine(array $inputs): array
    {
        $attendees = $this->requireNumeric($inputs, 'attendees');
        $hourlyRate = $this->requireNumeric($inputs, 'hourly_rate');
        $hours = $this->requireNumeric($inputs, 'hours');

        $totalCost = $attendees * $hourlyRate * $hours;

        return [
            'results' => [
                'total_cost' => $this->round($totalCost),
            ],
            'breakdown' => [
                'formula' => 'Total Cost = Attendees × Hourly Rate × Duration (hours).',
            ],
            'units' => [
                'total_cost' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 37. Salary raise impact.
     * ---------------------------------------------------------------
     */
    protected function raiseImpactEngine(array $inputs): array
    {
        $currentSalary = $this->requireNumeric($inputs, 'current_salary');
        $raisePercent = $this->requireNumeric($inputs, 'raise_percent');

        $newSalary = $currentSalary * (1 + $raisePercent / 100);
        $annualIncrease = $newSalary - $currentSalary;
        $monthlyIncrease = $annualIncrease / 12;

        return [
            'results' => [
                'new_salary' => $this->round($newSalary),
                'monthly_increase' => $this->round($monthlyIncrease),
                'annual_increase' => $this->round($annualIncrease),
            ],
            'breakdown' => [
                'formula' => 'New Salary = Current Salary × (1 + Raise% / 100).',
            ],
            'units' => [
                'new_salary' => 'currency',
                'monthly_increase' => 'currency',
                'annual_increase' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 38. Salary <-> hourly/biweekly/monthly.
     * ---------------------------------------------------------------
     */
    protected function salaryHourlyEngine(array $inputs): array
    {
        $salary = $this->requireNumeric($inputs, 'salary');
        $hoursPerWeek = $this->toFloat($inputs, 'hours_per_week', 40);

        $hourly = $this->safeDivide($salary, $hoursPerWeek * 52);
        $biweekly = $salary / 26;
        $monthly = $salary / 12;

        return [
            'results' => [
                'hourly' => $this->round($hourly, 2),
                'biweekly' => $this->round($biweekly),
                'monthly' => $this->round($monthly),
            ],
            'breakdown' => [
                'hours_per_week' => $hoursPerWeek,
                'formula' => 'Hourly = Annual Salary ÷ (Hours per Week × 52); Bi-weekly = Salary ÷ 26; Monthly = Salary ÷ 12.',
            ],
            'units' => [
                'hourly' => 'currency',
                'biweekly' => 'currency',
                'monthly' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 39. Generic compound growth (fallback engine).
     * ---------------------------------------------------------------
     */
    protected function genericGrowthEngine(array $inputs): array
    {
        $presentValue = $this->requireNumeric($inputs, 'present_value');
        $ratePercent = $this->requireNumeric($inputs, 'rate_percent');
        $years = $this->requireNumeric($inputs, 'years');

        $futureValue = $presentValue * (1 + $ratePercent / 100) ** $years;
        $totalGrowth = $futureValue - $presentValue;

        return [
            'results' => [
                'future_value' => $this->round($futureValue),
                'total_growth' => $this->round($totalGrowth),
            ],
            'breakdown' => [
                'present_value' => $this->round($presentValue),
                'rate_percent' => $this->round($ratePercent, 2),
                'years' => $this->round($years, 2),
                'formula' => 'Future Value = Present Value × (1 + Rate/100)^Years (standard compound growth).',
            ],
            'units' => [
                'future_value' => 'currency',
                'total_growth' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * 40. Percent-of calculation (fallback engine).
     * ---------------------------------------------------------------
     */
    protected function percentOfEngine(array $inputs): array
    {
        $amount = $this->requireNumeric($inputs, 'amount');
        $percent = $this->requireNumeric($inputs, 'percent');

        $result = $amount * $percent / 100;
        $remainder = $amount - $result;

        return [
            'results' => [
                'result' => $this->round($result, 4),
                'remainder' => $this->round($remainder, 4),
            ],
            'breakdown' => [
                'formula' => 'Result = Amount × Percent ÷ 100; Remainder = Amount − Result.',
            ],
            'units' => [],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * Extra: Lottery cash lump-sum vs annuity, net of tax.
     * ---------------------------------------------------------------
     */
    protected function lotteryAnnuityEngine(array $inputs): array
    {
        $jackpot = $this->requireNumeric($inputs, 'jackpot');
        $federalTaxPercent = $this->toFloat($inputs, 'federal_tax', 24);
        $stateTaxPercent = $this->toFloat($inputs, 'state_tax', 5);
        $annuityYears = max(1, $this->toFloat($inputs, 'annuity_years', 30));

        $taxRate = ($federalTaxPercent + $stateTaxPercent) / 100;
        $cashLumpGross = $jackpot * 0.60;
        $cashLumpNet = $cashLumpGross * (1 - $taxRate);

        $annuityAnnualGross = $jackpot / $annuityYears;
        $annuityAnnualNet = $annuityAnnualGross * (1 - $taxRate);
        $annuityTotalNet = $annuityAnnualNet * $annuityYears;

        return [
            'results' => [
                'cash_lump_net' => $this->round($cashLumpNet),
                'annuity_annual_net' => $this->round($annuityAnnualNet),
                'annuity_total_net' => $this->round($annuityTotalNet),
                'difference' => $this->round($annuityTotalNet - $cashLumpNet),
            ],
            'breakdown' => [
                'cash_lump_gross_estimate' => $this->round($cashLumpGross),
                'annuity_years' => $annuityYears,
                'combined_tax_rate_percent' => $this->round($taxRate * 100, 2),
                'formula' => 'Cash value ≈ 60% of advertised jackpot; both paths taxed at federal + state rate. Annuity is shown as a level 30-year payout (real Powerball/Mega Millions annuities grow ~5%/year).',
            ],
            'units' => [
                'cash_lump_net' => 'currency',
                'annuity_annual_net' => 'currency',
                'annuity_total_net' => 'currency',
                'difference' => 'currency',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * Extra: Roman numeral <-> integer.
     * ---------------------------------------------------------------
     */
    protected function romanNumeralEngine(array $inputs): array
    {
        $mode = $this->toString($inputs, 'mode', 'to_roman');

        if ($mode === 'to_number') {
            $roman = strtoupper($this->toString($inputs, 'roman', 'XIV'));
            $number = $this->romanToInt($roman);

            return [
                'results' => [
                    'number' => $number,
                    'roman' => $roman,
                ],
                'breakdown' => [
                    'formula' => 'Roman numerals are summed left to right, subtracting a value that precedes a larger one (subtractive notation).',
                ],
                'units' => [],
            ];
        }

        $number = (int) round($this->toFloat($inputs, 'number', 14));
        $number = max(1, min(3999, $number));
        $roman = $this->intToRoman($number);

        return [
            'results' => [
                'roman' => $roman,
                'number' => $number,
            ],
            'breakdown' => [
                'formula' => 'Standard subtractive Roman numeral notation, valid for 1–3999.',
            ],
            'units' => [],
        ];
    }

    protected function intToRoman(int $number): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];

        $result = '';
        foreach ($map as $value => $symbol) {
            while ($number >= $value) {
                $result .= $symbol;
                $number -= $value;
            }
        }

        return $result;
    }

    protected function romanToInt(string $roman): int
    {
        $values = ['I' => 1, 'V' => 5, 'X' => 10, 'L' => 50, 'C' => 100, 'D' => 500, 'M' => 1000];
        $total = 0;
        $length = strlen($roman);

        for ($i = 0; $i < $length; $i++) {
            $current = $values[$roman[$i]] ?? 0;
            $next = $values[$roman[$i + 1] ?? ''] ?? 0;

            $total += $current < $next ? -$current : $current;
        }

        return $total;
    }

    /**
     * ---------------------------------------------------------------
     * Extra: Shoe size cross-reference (approximate).
     * ---------------------------------------------------------------
     */
    protected function shoeSizeEngine(array $inputs): array
    {
        $usSize = $this->requireNumeric($inputs, 'us_size');
        $sex = $this->toString($inputs, 'sex', 'mens');

        if ($sex === 'womens') {
            $uk = $usSize - 2.5;
            $eu = $usSize + 31;
            $cm = $usSize * 0.847 + 16.9;
        } else {
            $uk = $usSize - 1;
            $eu = $usSize + 33;
            $cm = $usSize * 0.847 + 18.85;
        }

        return [
            'results' => [
                'uk_size' => $this->round($uk, 1),
                'eu_size' => $this->round($eu, 1),
                'cm' => $this->round($cm, 1),
            ],
            'breakdown' => [
                'sex' => $sex,
                'formula' => 'Approximate linear US↔UK/EU/cm cross-reference. Actual brand sizing varies — always check a specific brand\'s size chart.',
            ],
            'units' => [
                'cm' => 'cm',
            ],
        ];
    }

    /**
     * ---------------------------------------------------------------
     * Extra: Generic length converter (multi-unit dropdown).
     * ---------------------------------------------------------------
     */
    protected function multiLengthConvertEngine(array $inputs): array
    {
        $value = $this->requireNumeric($inputs, 'value');
        $fromUnit = $this->toString($inputs, 'from_unit', 'm');
        $toUnit = $this->toString($inputs, 'to_unit', 'ft');

        $metersFactor = [
            'mm' => 0.001, 'cm' => 0.01, 'm' => 1, 'km' => 1000,
            'in' => 0.0254, 'ft' => 0.3048, 'yd' => 0.9144, 'mi' => 1609.34,
        ];

        $from = $metersFactor[$fromUnit] ?? 1.0;
        $to = $metersFactor[$toUnit] ?? 1.0;

        $converted = $value * $from / $to;

        return [
            'results' => [
                'converted_value' => $this->round($converted, 6),
            ],
            'breakdown' => [
                'from_unit' => $fromUnit,
                'to_unit' => $toUnit,
                'formula' => 'Converted Value = Value × (From Unit ÷ meters) ÷ (To Unit ÷ meters).',
            ],
            'units' => [
                'converted_value' => $toUnit,
            ],
        ];
    }

    protected function money(float $value): string
    {
        return '$'.number_format($value, 0);
    }
}
