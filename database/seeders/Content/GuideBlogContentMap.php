<?php

namespace Database\Seeders\Content;

use Database\Seeders\Content\GuideBlogs\AanaToSquareMetreNepalLand;
use Database\Seeders\Content\GuideBlogs\CementSandPlasteringQuantity;
use Database\Seeders\Content\GuideBlogs\ConcreteMixRatiosExplained;
use Database\Seeders\Content\GuideBlogs\DailyCalorieNeedsBmrTdee;
use Database\Seeders\Content\GuideBlogs\DashainAllowanceCalculatorGuide;
use Database\Seeders\Content\GuideBlogs\ElectricityBillAndAcSizeBasics;
use Database\Seeders\Content\GuideBlogs\EmiExplainedLoanPayments;
use Database\Seeders\Content\GuideBlogs\FdAndRoiCompareReturns;
use Database\Seeders\Content\GuideBlogs\FuelCostCalculatorTripBudget;
use Database\Seeders\Content\GuideBlogs\GpaVsCgpaExplained;
use Database\Seeders\Content\GuideBlogs\GstVatHowTaxIsAdded;
use Database\Seeders\Content\GuideBlogs\HouseCostAndRebarBudgetChecks;
use Database\Seeders\Content\GuideBlogs\HowMuchPaintForARoom;
use Database\Seeders\Content\GuideBlogs\HowToCalculateAgeAccurately;
use Database\Seeders\Content\GuideBlogs\HowToCalculateBmiCorrectly;
use Database\Seeders\Content\GuideBlogs\HowToCalculateBricksForAWall;
use Database\Seeders\Content\GuideBlogs\HowToUseOnlineCalculatorsAccurately;
use Database\Seeders\Content\GuideBlogs\LengthConverterMetresFeetInches;
use Database\Seeders\Content\GuideBlogs\MortgageAffordabilityBasics;
use Database\Seeders\Content\GuideBlogs\NepalDrivingLicenceFeeGuide;
use Database\Seeders\Content\GuideBlogs\PercentageCalculatorDiscountsMarksGrowth;
use Database\Seeders\Content\GuideBlogs\ProfitMarginVsMarkup;
use Database\Seeders\Content\GuideBlogs\SipAndCompoundInterestForBeginners;
use Database\Seeders\Content\GuideBlogs\SleepCalculatorSmarterBedtime;
use Database\Seeders\Content\GuideBlogs\TileQuantityEstimationGuide;
use Database\Seeders\Content\GuideBlogs\TipAndSplitBillFairSharing;

/**
 * Maps BlogSeeder slugs to long-form (≥1200 word) HTML article classes.
 */
class GuideBlogContentMap
{
    /**
     * @return array<string, class-string>
     */
    public static function classes(): array
    {
        return [
            'how-to-calculate-bricks-for-a-wall' => HowToCalculateBricksForAWall::class,
            'concrete-mix-ratios-explained' => ConcreteMixRatiosExplained::class,
            'how-much-paint-for-a-room' => HowMuchPaintForARoom::class,
            'tile-quantity-estimation-guide' => TileQuantityEstimationGuide::class,
            'emi-explained-loan-payments' => EmiExplainedLoanPayments::class,
            'mortgage-affordability-basics' => MortgageAffordabilityBasics::class,
            'sip-and-compound-interest-for-beginners' => SipAndCompoundInterestForBeginners::class,
            'how-to-calculate-bmi-correctly' => HowToCalculateBmiCorrectly::class,
            'daily-calorie-needs-bmr-tdee' => DailyCalorieNeedsBmrTdee::class,
            'gpa-vs-cgpa-explained' => GpaVsCgpaExplained::class,
            'cement-sand-plastering-quantity' => CementSandPlasteringQuantity::class,
            'how-to-use-online-calculators-accurately' => HowToUseOnlineCalculatorsAccurately::class,
            'how-to-calculate-age-accurately' => HowToCalculateAgeAccurately::class,
            'length-converter-metres-feet-inches' => LengthConverterMetresFeetInches::class,
            'percentage-calculator-discounts-marks-growth' => PercentageCalculatorDiscountsMarksGrowth::class,
            'nepal-driving-licence-fee-guide' => NepalDrivingLicenceFeeGuide::class,
            'aana-to-square-metre-nepal-land' => AanaToSquareMetreNepalLand::class,
            'dashain-allowance-calculator-guide' => DashainAllowanceCalculatorGuide::class,
            'gst-vat-how-tax-is-added' => GstVatHowTaxIsAdded::class,
            'tip-and-split-bill-fair-sharing' => TipAndSplitBillFairSharing::class,
            'fuel-cost-calculator-trip-budget' => FuelCostCalculatorTripBudget::class,
            'electricity-bill-and-ac-size-basics' => ElectricityBillAndAcSizeBasics::class,
            'fd-and-roi-compare-returns' => FdAndRoiCompareReturns::class,
            'profit-margin-vs-markup' => ProfitMarginVsMarkup::class,
            'house-cost-and-rebar-budget-checks' => HouseCostAndRebarBudgetChecks::class,
            'sleep-calculator-smarter-bedtime' => SleepCalculatorSmarterBedtime::class,
        ];
    }

    public static function html(string $slug): ?string
    {
        $class = self::classes()[$slug] ?? null;

        if ($class === null || ! method_exists($class, 'html')) {
            return null;
        }

        $html = $class::html();

        return is_string($html) && $html !== '' ? $html : null;
    }

    public static function wordCount(string $html): int
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if ($text === '') {
            return 0;
        }

        preg_match_all('/[\p{L}\p{N}\']+/u', $text, $matches);

        return count($matches[0]);
    }
}
