<?php

namespace Database\Seeders;

use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Models\CalculatorExample;
use App\Models\CalculatorFaq;
use App\Services\Calculators\CalculatorRegistry;
use App\Services\Seo\CalculatorContentBuilder;
use App\Support\CalculatorIconMap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Seeds the eight calculator categories and every calculator handler
 * discovered by the CalculatorRegistry, along with SEO-quality copy,
 * three FAQs and one worked example per calculator.
 *
 * The worked example's inputs/outputs are not hand-typed: they're built
 * from each handler's own inputSchema() defaults (with a handful of
 * overrides for handlers whose required fields - dates, times, course
 * lists - have no sensible schema default) and then run through the
 * handler's real calculate() method, so the stored example always
 * matches the handler's actual current behaviour.
 *
 * Safe to re-run: categories and calculators are upserted by slug, and
 * each calculator's FAQs/example are replaced on every run.
 */
class CalculatorSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected const CATEGORIES = [
        [
            'slug' => 'construction',
            'name' => 'Construction Calculators',
            'icon' => 'fas fa-hard-hat',
            'sort_order' => 1,
            'description' => 'Estimate materials and costs for building projects - bricks, cement, concrete, steel, tiles, paint, sand, aggregate, plaster and excavation - with accurate, engineering-based formulas.',
            'meta_title' => 'Construction Calculators - Material & Cost Estimators | Calculator Hub',
            'meta_description' => 'Free construction calculators for bricks, cement, concrete, steel, tiles, paint, sand, aggregate, plaster and excavation. Accurate material estimates for any project size.',
        ],
        [
            'slug' => 'finance',
            'name' => 'Finance Calculators',
            'icon' => 'fas fa-coins',
            'sort_order' => 2,
            'description' => 'Plan loans, investments and taxes with precision - EMI, mortgage, ROI, SIP, compound interest, GST/VAT and more, all powered by industry-standard financial formulas.',
            'meta_title' => 'Finance Calculators - Loans, Investments & Tax | Calculator Hub',
            'meta_description' => 'Free finance calculators for EMI, loans, mortgages, ROI, SIP, compound interest, GST, VAT, salary tax, profit and margin. Make informed financial decisions instantly.',
        ],
        [
            'slug' => 'health',
            'name' => 'Health & Fitness Calculators',
            'icon' => 'fas fa-heartbeat',
            'sort_order' => 3,
            'description' => 'Track your health and fitness with BMI, BMR, body fat, calorie and water intake calculators built on established medical formulas.',
            'meta_title' => 'Health & Fitness Calculators - BMI, BMR, Calories | Calculator Hub',
            'meta_description' => 'Free health calculators for BMI, BMR, body fat percentage, daily calorie needs and water intake. Evidence-based formulas for your fitness goals.',
        ],
        [
            'slug' => 'education',
            'name' => 'Education Calculators',
            'icon' => 'fas fa-graduation-cap',
            'sort_order' => 4,
            'description' => 'Calculate your academic performance with GPA, CGPA and percentage calculators trusted by students worldwide.',
            'meta_title' => 'Education Calculators - GPA, CGPA & Percentage | Calculator Hub',
            'meta_description' => 'Free education calculators for GPA, CGPA and percentage calculations. Accurate, credit-weighted results for students and academics.',
        ],
        [
            'slug' => 'business',
            'name' => 'Business Calculators',
            'icon' => 'fas fa-briefcase',
            'sort_order' => 5,
            'description' => 'Streamline business decisions with discount, commission and payroll calculators designed for retailers, sales teams and HR.',
            'meta_title' => 'Business Calculators - Discount, Commission & Payroll | Calculator Hub',
            'meta_description' => 'Free business calculators for discounts, sales commission and employee payroll. Fast, accurate results for everyday business operations.',
        ],
        [
            'slug' => 'unit-conversion',
            'name' => 'Unit Conversion Calculators',
            'icon' => 'fas fa-exchange-alt',
            'sort_order' => 6,
            'description' => 'Convert length, area, volume, weight, temperature and speed between metric and imperial units instantly and accurately.',
            'meta_title' => 'Unit Converters - Length, Area, Weight, Temperature & Speed | Calculator Hub',
            'meta_description' => 'Free unit converters for length, area, volume, weight, temperature and speed. Convert between metric and imperial units instantly.',
        ],
        [
            'slug' => 'daily-life',
            'name' => 'Daily Life Calculators',
            'icon' => 'fas fa-calendar-day',
            'sort_order' => 7,
            'description' => 'Handy everyday calculators for age, dates and time differences - perfect for quick personal and planning calculations.',
            'meta_title' => 'Daily Life Calculators - Age, Date & Time Difference | Calculator Hub',
            'meta_description' => 'Free daily life calculators for age, date differences and time durations. Quick, accurate everyday calculations.',
        ],
        [
            'slug' => 'engineering',
            'name' => 'Engineering Calculators',
            'icon' => 'fas fa-drafting-compass',
            'sort_order' => 8,
            'description' => 'Structural, electrical and mechanical engineering calculators for beams, pipes, power, solar and more.',
            'meta_title' => 'Engineering Calculators - Beam, Pipe, Power & Solar | Calculator Hub',
            'meta_description' => 'Free engineering calculators for beams, slabs, pipe flow, Ohm’s law, solar, battery backup and UPS sizing.',
        ],
        [
            'slug' => 'basic-math',
            'name' => 'Basic Math Calculators',
            'icon' => 'fas fa-square-root-alt',
            'sort_order' => 9,
            'description' => 'Fractions, averages, statistics, logs, primes, GCD/LCM and other core math tools.',
            'meta_title' => 'Basic Math Calculators - Fraction, Average, Stats | Calculator Hub',
            'meta_description' => 'Free basic math calculators for fractions, ratios, averages, standard deviation, logs, factorials and more.',
        ],
        [
            'slug' => 'fitness',
            'name' => 'Fitness Calculators',
            'icon' => 'fas fa-dumbbell',
            'sort_order' => 10,
            'description' => 'Training calculators for TDEE, pace, one-rep max, calories burned and heart-rate zones.',
            'meta_title' => 'Fitness Calculators - TDEE, Pace, 1RM | Calculator Hub',
            'meta_description' => 'Free fitness calculators for TDEE, running pace, calories burned, VO2 max and one-rep max.',
        ],
        [
            'slug' => 'home',
            'name' => 'Home Calculators',
            'icon' => 'fas fa-home',
            'sort_order' => 11,
            'description' => 'Everyday home tools for electricity bills, AC sizing, solar needs, room and carpet area.',
            'meta_title' => 'Home Calculators - Electricity, AC, Solar | Calculator Hub',
            'meta_description' => 'Free home calculators for electricity bills, AC size, solar requirements, room area and curtains.',
        ],
        [
            'slug' => 'automobile',
            'name' => 'Automobile Calculators',
            'icon' => 'fas fa-car',
            'sort_order' => 12,
            'description' => 'True cost per mile, auto refinance, tire size, lease vs buy, EV vs gas and commute cost calculators.',
            'meta_title' => 'Automobile Calculators - Cost, Refi, EV, Commute | Calculator Hub',
            'meta_description' => 'Free automobile calculators for true cost per mile, refinance, tire size, lease vs buy, EV vs gas and commute cost.',
        ],
        [
            'slug' => 'climate-energy',
            'name' => 'Climate & Energy Calculators',
            'icon' => 'fas fa-solar-panel',
            'sort_order' => 16,
            'description' => 'Solar ROI, backup power, EV vs ICE TCO, heat pumps, insulation, carbon footprint and climate risk tools.',
            'meta_title' => 'Climate & Energy Calculators | Calculator Hub',
            'meta_description' => 'Free climate and energy calculators for solar ROI, heat pumps, electrification, carbon footprint and climate migration.',
        ],
        [
            'slug' => 'tax-deductions',
            'name' => 'Tax & Deductions Calculators',
            'icon' => 'fas fa-file-invoice-dollar',
            'sort_order' => 17,
            'description' => 'US federal tax, equity compensation, property tax, and multi-country bracket tools.',
            'meta_title' => 'Tax & Deductions Calculators | Calculator Hub',
            'meta_description' => 'Free tax calculators for bonus tax, Roth strategies, RSU withholding, capital gains, quarterly estimates and nomad residency.',
        ],
        [
            'slug' => 'productivity',
            'name' => 'Productivity Calculators',
            'icon' => 'fas fa-brain',
            'sort_order' => 18,
            'description' => 'Compound habits, success odds, cognitive load, move timing and decision fatigue tools.',
            'meta_title' => 'Productivity Calculators | Calculator Hub',
            'meta_description' => 'Free productivity calculators for compound habits, probability of success, cognitive load, best day to move and decision fatigue.',
        ],
        [
            'slug' => 'agriculture',
            'name' => 'Agriculture Calculators',
            'icon' => 'fas fa-seedling',
            'sort_order' => 13,
            'description' => 'Seed, fertilizer, irrigation, livestock feed and crop yield estimators for farms.',
            'meta_title' => 'Agriculture Calculators - Seed, Fertilizer, Yield | Calculator Hub',
            'meta_description' => 'Free agriculture calculators for seed rate, fertilizer, irrigation water, feed and crop yield.',
        ],
        [
            'slug' => 'real-estate',
            'name' => 'Real Estate Calculators',
            'icon' => 'fas fa-building',
            'sort_order' => 14,
            'description' => 'Rent affordability, property tax and rental yield tools for property decisions.',
            'meta_title' => 'Real Estate Calculators - Rent, Yield, Tax | Calculator Hub',
            'meta_description' => 'Free real estate calculators for rent affordability, property tax and rental yield.',
        ],
        [
            'slug' => 'internet-it',
            'name' => 'Internet & IT Tools',
            'icon' => 'fas fa-laptop-code',
            'sort_order' => 15,
            'description' => 'Practical IT utilities such as password strength checking.',
            'meta_title' => 'Internet & IT Calculators | Calculator Hub',
            'meta_description' => 'Free internet and IT tools including password strength checking.',
        ],
        [
            'slug' => 'developer',
            'name' => 'AI & Developer Tools',
            'icon' => 'fas fa-code',
            'sort_order' => 16,
            'description' => 'Token and API cost estimators for developers using AI and cloud APIs.',
            'meta_title' => 'Developer Calculators - API & Token Cost | Calculator Hub',
            'meta_description' => 'Free developer calculators for OpenAI token cost and API usage pricing.',
        ],
        [
            'slug' => 'nepal',
            'name' => 'Nepal Calculators',
            'icon' => 'fas fa-flag',
            'sort_order' => 17,
            'description' => 'Nepal-focused tools for income tax, VAT, TDS, NEPSE brokerage, land measurement and more.',
            'meta_title' => 'Nepal Calculators - Tax, NEPSE, Land | Calculator Hub',
            'meta_description' => 'Free Nepal calculators for income tax, VAT, TDS, NEPSE brokerage, Ropani/Aana land conversion and Dashain allowance.',
        ],
    ];

    /**
     * Maps every registered handler key to its category slug.
     *
     * @var array<string, string>
     */
    protected const CATEGORY_MAP = [
        // Construction
        'brick_calculator' => 'construction',
        'cement_calculator' => 'construction',
        'concrete_calculator' => 'construction',
        'steel_calculator' => 'construction',
        'tile_calculator' => 'construction',
        'paint_calculator' => 'construction',
        'sand_calculator' => 'construction',
        'aggregate_calculator' => 'construction',
        'plaster_calculator' => 'construction',
        'excavation_calculator' => 'construction',

        // Finance
        'emi_calculator' => 'finance',
        'loan_calculator' => 'finance',
        'mortgage_calculator' => 'finance',
        'profit_calculator' => 'finance',
        'margin_calculator' => 'finance',
        'gst_calculator' => 'finance',
        'vat_calculator' => 'finance',
        'salary_tax_calculator' => 'finance',
        'roi_calculator' => 'finance',
        'sip_calculator' => 'finance',
        'compound_interest_calculator' => 'finance',

        // Health
        'bmi_calculator' => 'health',
        'bmr_calculator' => 'health',
        'body_fat_calculator' => 'health',
        'calorie_calculator' => 'health',
        'water_intake_calculator' => 'health',

        // Education
        'gpa_calculator' => 'education',
        'percentage_calculator' => 'education',
        'cgpa_calculator' => 'education',

        // Business
        'discount_calculator' => 'business',
        'commission_calculator' => 'business',
        'payroll_calculator' => 'business',

        // Unit conversion
        'length_converter' => 'unit-conversion',
        'area_converter' => 'unit-conversion',
        'volume_converter' => 'unit-conversion',
        'weight_converter' => 'unit-conversion',
        'temperature_converter' => 'unit-conversion',
        'speed_converter' => 'unit-conversion',

        // Daily life
        'age_calculator' => 'daily-life',
        'date_difference_calculator' => 'daily-life',
        'date_converter_calculator' => 'daily-life',
        'time_calculator' => 'daily-life',

        // Engineering
        'beam_calculator' => 'engineering',
        'slab_calculator' => 'engineering',
        'column_calculator' => 'engineering',
        'footing_calculator' => 'engineering',

        // Automobile & commute (explicit — also in generated_category_map.php)
        'true_cost_per_mile_calculator' => 'automobile',
        'auto_refinance_calculator' => 'automobile',
        'lease_vs_buy_car_calculator' => 'automobile',
        'ev_vs_gas_total_cost_calculator' => 'automobile',
        'commute_cost_time_calculator' => 'automobile',
        'tire_size_calculator' => 'automobile',
        'fuel_cost_calculator' => 'automobile',
        'mileage_calculator' => 'automobile',
        'ev_charging_calculator' => 'automobile',
        'road_trip_cost_calculator' => 'automobile',
        'vehicle_speed_calculator' => 'automobile',

        // Climate & Energy
        'solar_roi_calculator' => 'climate-energy',
        'backup_power_roi_calculator' => 'climate-energy',
        'solar_panel_cost_calculator' => 'climate-energy',
        'ev_vs_ice_tco_calculator' => 'climate-energy',
        'ac_size_calculator' => 'climate-energy',
        'heat_pump_payback_calculator' => 'climate-energy',
        'home_insulation_roi_calculator' => 'climate-energy',
        'whole_home_electrification_bundle_roi_calculator' => 'climate-energy',
        'flood_insurance_vs_self_insure_calculator' => 'climate-energy',
        'home_climate_hardening_payback_calculator' => 'climate-energy',
        'wildfire_defensible_space_roi_calculator' => 'climate-energy',
        'carbon_footprint_true_cost_calculator' => 'climate-energy',
        'flight_emissions_offset_calculator' => 'climate-energy',
        'electricity_bill_optimizer_tou_calculator' => 'climate-energy',
        'climate_migration_cost_calculator' => 'climate-energy',

        // Tax & Deductions
        'kids_529_vs_utma_vs_roth_calculator' => 'tax-deductions',
        'multi_state_remote_work_tax_exposure_calculator' => 'tax-deductions',
        'hsa_triple_tax_optimizer_calculator' => 'tax-deductions',
        'bonus_tax_calculator' => 'tax-deductions',
        'mega_backdoor_roth_calculator' => 'tax-deductions',
        'backdoor_roth_pro_rata_trap_calculator' => 'tax-deductions',
        'property_tax_calculator' => 'tax-deductions',
        'quarterly_estimated_tax_calculator' => 'tax-deductions',
        'dependent_care_fsa_vs_child_tax_credit_calculator' => 'tax-deductions',
        'capital_gains_tax_calculator' => 'tax-deductions',
        'after_tax_income_calculator' => 'tax-deductions',
        'rsu_tax_withholding_shortfall_calculator' => 'tax-deductions',
        'stock_options_iso_nso_amt_calculator' => 'tax-deductions',
        'tax_bracket_calculator' => 'tax-deductions',
        'digital_nomad_tax_residency_optimizer_calculator' => 'tax-deductions',

        // Productivity
        'compound_habit_calculator' => 'productivity',
        'probability_of_success_calculator' => 'productivity',
        'cognitive_load_calculator' => 'productivity',
        'best_day_to_move_calculator' => 'productivity',
        'decision_fatigue_calculator' => 'productivity',
    ];

    /**
     * Curated homepage-featured calculators, one from most categories.
     *
     * @var array<int, string>
     */
    protected const FEATURED_KEYS = [
        'emi_calculator',
        'bmi_calculator',
        'brick_calculator',
        'percentage_calculator',
        'length_converter',
        'age_calculator',
        'loan_calculator',
        'gpa_calculator',
    ];

    /**
     * Premium calculators — specialized / professional tools.
     * Everything else stays free for SEO traffic and freemium conversion.
     *
     * Free (not listed): unit converters, daily life, education, core
     * construction (brick/cement/concrete/paint/tile/sand), high-traffic
     * finance (EMI/loan/GST/VAT/profit/compound interest), BMI/water/calorie,
     * and discount.
     *
     * @var array<int, string>
     */
    protected const PREMIUM_KEYS = [
        // Construction — specialized material / site work
        'steel_calculator',
        'aggregate_calculator',
        'plaster_calculator',
        'excavation_calculator',

        // Finance — planning / investment / tax depth
        'mortgage_calculator',
        'margin_calculator',
        'salary_tax_calculator',
        'roi_calculator',
        'sip_calculator',

        // Health — advanced fitness metrics
        'bmr_calculator',
        'body_fat_calculator',

        // Business — HR / sales ops
        'commission_calculator',
        'payroll_calculator',

        // Engineering — structural design (all premium)
        'beam_calculator',
        'slab_calculator',
        'column_calculator',
        'footing_calculator',

        // Newer specialized tools
        'rcc_calculator',
        'rebar_calculator',
        'boq_calculator',
        'house_cost_calculator',
        'pressure_drop_calculator',
        'cable_size_calculator',
        'solar_panel_calculator',
        'transformer_calculator',
        'retirement_calculator',
        'mutual_fund_calculator',
        'fd_calculator',
        'break_even_calculator',
        'tdee_calculator',
        'vo2_max_calculator',
        'nepal_income_tax_calculator',
        'nepse_brokerage_calculator',
        'nepal_house_cost_calculator',
        'land_measurement_nepal_calculator',
        'ebitda_calculator',
        'rental_yield_calculator',
    ];

    /**
     * Sample inputs for handlers whose required fields have no usable
     * schema default (dates, times and repeatable item lists).
     *
     * @var array<string, array<string, mixed>>
     */
    protected const SAMPLE_OVERRIDES = [
        'age_calculator' => [
            'birth_date' => '1995-06-15',
        ],
        'date_difference_calculator' => [
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ],
        'date_converter_calculator' => [
            'direction' => 'ad_to_bs',
            'ad_date' => '2026-07-23',
            'bs_date' => '2083-04-07',
        ],
        'time_calculator' => [
            'mode' => 'difference',
            'start_time' => '09:00',
            'end_time' => '17:30',
        ],
        'gpa_calculator' => [
            'courses' => [
                ['name' => 'Mathematics', 'credits' => 3, 'grade' => 'A'],
                ['name' => 'Physics', 'credits' => 4, 'grade' => 'B+'],
                ['name' => 'English Literature', 'credits' => 2, 'grade' => 'A-'],
            ],
        ],
        'cgpa_calculator' => [
            'semesters' => [
                ['name' => 'Semester 1', 'gpa' => 3.6, 'credits' => 15],
                ['name' => 'Semester 2', 'gpa' => 3.8, 'credits' => 16],
            ],
        ],
    ];

    /**
     * Per-calculator SEO copy, formula explanation and FAQs, keyed by
     * the handler's key().
     *
     * @var array<string, array<string, mixed>>
     */
    protected const CALCULATOR_META = [
        'brick_calculator' => [
            'title' => 'Brick Calculator',
            'icon' => 'fas fa-cubes',
            'short_description' => 'Estimate the exact number of bricks and mortar volume needed to build a wall of any size.',
            'description' => "The Brick Calculator estimates how many bricks and how much mortar you need to build a wall, based on the wall's dimensions, the brick size you're using, and the mortar joint thickness. It automatically adds a wastage allowance to account for breakage and cutting, so your material order matches what actually gets used on site.",
            'formula_description' => 'Bricks required = Wall Volume ÷ (Brick Volume + Mortar Joint Volume), with a wastage percentage added on top. Mortar volume is the difference between the wall\'s total volume and the combined volume of all bricks used.',
            'meta_title' => 'Brick Calculator - Estimate Bricks & Mortar Needed | Calculator Hub',
            'meta_description' => 'Calculate exactly how many bricks and how much mortar you need for your wall. Enter wall size, brick dimensions and mortar thickness for an instant, accurate estimate.',
            'faqs' => [
                ['How many bricks do I need for a 10m x 3m wall?', 'It depends on brick size, wall thickness and mortar joint thickness. Enter your wall dimensions and brick size into the calculator above to get an exact figure, including a wastage allowance.'],
                ['How much wastage should I add when ordering bricks?', 'A wastage allowance of 5-10% is standard practice to account for breakage, cutting and on-site handling losses. The calculator defaults to 5%, which you can adjust based on the complexity of your project.'],
                ['Does the calculator account for mortar joints?', 'Yes. You can set the mortar joint thickness (typically 10mm), and the calculator factors this into both the brick count and the total mortar volume required.'],
            ],
        ],
        'cement_calculator' => [
            'title' => 'Cement Calculator',
            'icon' => 'fas fa-weight-hanging',
            'short_description' => 'Work out how many cement bags you need for a concrete or mortar mix from its wet volume and ratio.',
            'description' => 'The Cement Calculator converts a wet concrete or mortar volume into the number of 50kg cement bags required, based on your chosen mix ratio (cement:sand:aggregate). It also reports the corresponding sand and aggregate volumes so you can order all materials in one go.',
            'formula_description' => 'Dry volume = Wet Volume × Dry Volume Factor (typically 1.54). Each material\'s volume is its share of the mix ratio applied to the dry volume, and cement bags = Cement Weight ÷ 50kg.',
            'meta_title' => 'Cement Bag Calculator - Cement, Sand & Aggregate Quantity | Calculator Hub',
            'meta_description' => 'Calculate the number of cement bags, plus sand and aggregate volume, needed for your concrete or mortar mix. Enter volume and mix ratio for an instant estimate.',
            'faqs' => [
                ['Why is the dry volume higher than the wet volume?', 'Dry, loose materials (cement, sand, aggregate) occupy more space than they do once mixed with water and compacted, so a dry volume factor of around 1.54 is applied to the wet volume before calculating material quantities.'],
                ['What mix ratio should I use?', 'Common ratios are 1:2:4 for general RCC work and 1:1.5:3 for higher-strength applications. Check your project\'s structural specification or local building code for the recommended ratio.'],
                ['How much does one bag of cement weigh?', 'A standard cement bag weighs 50kg, which is the figure this calculator uses to convert cement weight into the number of bags to order.'],
            ],
        ],
        'concrete_calculator' => [
            'title' => 'Concrete Calculator',
            'icon' => 'fas fa-cube',
            'short_description' => 'Calculate concrete volume and the cement, sand, aggregate and water needed for your pour.',
            'description' => 'The Concrete Calculator computes the volume of concrete needed for a slab, footing or column pour from its length, width and thickness, then breaks that volume down into cement bags, sand, aggregate and water using your chosen mix ratio and water-cement ratio.',
            'formula_description' => 'Concrete volume = Length × Width × Thickness. This wet volume is converted to a dry volume (×1.54) and split between cement, sand and aggregate according to the mix ratio; water is derived from the cement weight and water-cement ratio.',
            'meta_title' => 'Concrete Calculator - Cement, Sand, Aggregate & Water | Calculator Hub',
            'meta_description' => 'Calculate concrete volume and the exact cement bags, sand, aggregate and water quantities needed for your slab, footing or column pour.',
            'faqs' => [
                ['How do I calculate how much concrete I need for a slab?', 'Multiply the slab\'s length, width and thickness (in meters) to get the volume in cubic meters. The calculator does this automatically and also gives you the material breakdown.'],
                ['What water-cement ratio should I use?', 'A water-cement ratio of 0.4-0.5 is typical for structural concrete; lower ratios increase strength but reduce workability. The calculator defaults to 0.45, a good general-purpose value.'],
                ['Does this include reinforcement steel?', 'No, this calculator covers the concrete mix only. Use the Steel Calculator or Slab Calculator to estimate reinforcement steel weight separately.'],
            ],
        ],
        'steel_calculator' => [
            'title' => 'Steel Weight Calculator',
            'icon' => 'fas fa-grip-lines',
            'short_description' => 'Calculate the total weight and cost of reinforcement steel bars from their diameter and length.',
            'description' => 'The Steel Weight Calculator converts reinforcement bar diameter and length into total weight using the standard unit-weight formula, then applies a wastage allowance and an optional rate per kg to estimate material cost.',
            'formula_description' => 'Unit weight per meter (kg/m) = diameter² ÷ 162. Total weight = unit weight × total bar length × number of bars, with a wastage percentage added for cutting losses.',
            'meta_title' => 'Steel Weight Calculator - Rebar Weight & Cost | Calculator Hub',
            'meta_description' => 'Calculate reinforcement steel bar weight and estimated cost from bar diameter, length and quantity. Uses the standard d²/162 unit weight formula.',
            'faqs' => [
                ['What is the formula for steel bar weight?', 'The standard formula is weight (kg/m) = diameter² ÷ 162, where diameter is in millimeters. This calculator applies that formula automatically to your bar size and length.'],
                ['How much wastage should I allow for steel bars?', 'A wastage allowance of 3-5% is typical to account for cutting and lapping losses; the calculator defaults to 3%, which you can adjust for your project.'],
                ['Can I calculate the cost of steel bars too?', 'Yes, enter your rate per kilogram and the calculator will multiply it by the total weight (including wastage) to give you an estimated material cost.'],
            ],
        ],
        'tile_calculator' => [
            'title' => 'Tile Calculator',
            'icon' => 'fas fa-border-all',
            'short_description' => 'Find out how many floor or wall tiles and boxes you need to cover a room.',
            'description' => 'The Tile Calculator works out how many tiles are needed to cover a room\'s floor or wall area based on room and tile dimensions, then converts that into the number of boxes to purchase and adds a wastage allowance for cuts and breakage.',
            'formula_description' => 'Tiles required = (Room Area ÷ Tile Area) × (1 + Wastage %). Boxes required = Tiles Required ÷ Tiles per Box, rounded up to the nearest whole box.',
            'meta_title' => 'Tile Calculator - Tiles & Boxes Needed for Your Room | Calculator Hub',
            'meta_description' => 'Calculate exactly how many tiles and boxes you need for your floor or wall, based on room size, tile size and wastage allowance.',
            'faqs' => [
                ['How many tiles do I need for a 20m² room?', 'It depends on your tile size. Enter your room dimensions and tile dimensions into the calculator above and it will compute the exact number of tiles and boxes needed.'],
                ['How much extra tile should I buy for wastage?', '10% extra is a common allowance for cutting losses and breakage, especially for rooms with many corners or diagonal layouts. The calculator defaults to 10% but you can adjust it.'],
                ['Why does the calculator ask for tiles per box?', "Tiles are usually sold by the box. Entering tiles per box lets the calculator tell you exactly how many full boxes to purchase, rounding up so you don't run short."],
            ],
        ],
        'paint_calculator' => [
            'title' => 'Paint Calculator',
            'icon' => 'fas fa-paint-roller',
            'short_description' => 'Estimate how many liters of paint you need for a wall, accounting for coats and openings.',
            'description' => "The Paint Calculator estimates the liters of paint required to cover a wall area, after deducting doors and windows, based on the paint's coverage rate and the number of coats you plan to apply. It also estimates total cost if you provide a price per liter.",
            'formula_description' => 'Paintable area = Wall Area − Door/Window Area. Paint required (liters) = (Paintable Area × Number of Coats) ÷ Coverage per Liter.',
            'meta_title' => 'Paint Calculator - Liters of Paint Needed | Calculator Hub',
            'meta_description' => 'Calculate how many liters of paint you need for your walls, accounting for doors, windows, number of coats and coverage rate.',
            'faqs' => [
                ['How much paint do I need for two coats?', 'Enter the number of coats into the calculator and it will multiply the paintable area accordingly - two coats roughly doubles the paint quantity needed for one coat.'],
                ['What is a typical paint coverage rate?', "Most paints cover 8-12 m² per liter, depending on the surface and paint type. Check your paint tin's label for the manufacturer's exact coverage rate."],
                ['Should I subtract doors and windows from the wall area?', 'Yes, subtracting door and window area gives a more accurate paint estimate. Enter the total area of openings and the calculator will deduct it automatically.'],
            ],
        ],
        'sand_calculator' => [
            'title' => 'Sand Calculator',
            'icon' => 'fas fa-mound',
            'short_description' => 'Calculate the sand volume and weight needed for a concrete or mortar mix.',
            'description' => 'The Sand Calculator determines how much sand (in volume, weight and tons) is required for a concrete or mortar mix, based on the wet volume of the mix and your chosen cement:sand:aggregate ratio.',
            'formula_description' => 'Dry volume = Wet Volume × Dry Volume Factor. Sand volume = (Dry Volume × Sand Ratio) ÷ Total Ratio Parts. Sand weight = Sand Volume × 1600 kg/m³.',
            'meta_title' => 'Sand Calculator - Sand Quantity for Concrete & Mortar | Calculator Hub',
            'meta_description' => 'Calculate the exact volume, weight and tonnage of sand needed for your concrete or mortar mix based on wet volume and mix ratio.',
            'faqs' => [
                ['How much sand do I need for 1m³ of concrete?', 'It depends on your mix ratio. For a common 1:2:4 mix, roughly 0.42m³ of sand is needed per cubic meter of wet concrete - enter your own figures above for a precise result.'],
                ['What density does the calculator use for sand?', 'It uses a typical dry sand density of 1600 kg/m³ to convert sand volume into weight and tons.'],
                ['Does sand quantity depend on the aggregate ratio?', 'Yes, sand is one part of the total mix ratio (cement:sand:aggregate), so changing any ratio part changes how the dry volume is distributed among all three materials.'],
            ],
        ],
        'aggregate_calculator' => [
            'title' => 'Aggregate Calculator',
            'icon' => 'fas fa-mountain',
            'short_description' => 'Calculate the coarse aggregate volume and weight needed for a concrete mix.',
            'description' => 'The Aggregate Calculator determines the volume, weight and tonnage of coarse aggregate (crushed stone/gravel) required for a concrete mix, based on the mix\'s wet volume and cement:sand:aggregate ratio.',
            'formula_description' => 'Dry volume = Wet Volume × Dry Volume Factor. Aggregate volume = (Dry Volume × Aggregate Ratio) ÷ Total Ratio Parts. Aggregate weight = Aggregate Volume × 1550 kg/m³.',
            'meta_title' => 'Aggregate Calculator - Coarse Aggregate for Concrete | Calculator Hub',
            'meta_description' => 'Calculate the volume, weight and tonnage of coarse aggregate needed for your concrete mix based on wet volume and mix ratio.',
            'faqs' => [
                ['What density does this calculator assume for aggregate?', 'It uses a typical coarse aggregate density of 1550 kg/m³ to convert volume into weight and metric tons.'],
                ['How does the mix ratio affect aggregate quantity?', "Aggregate is one part of the cement:sand:aggregate ratio (e.g. the '4' in 1:2:4). A higher aggregate ratio part means a larger share of the dry volume is allocated to aggregate."],
                ['Can I use this for both footings and slabs?', 'Yes, the calculation is based purely on wet volume and mix ratio, so it works for any concrete element - just calculate the volume of your specific element first.'],
            ],
        ],
        'plaster_calculator' => [
            'title' => 'Plaster Calculator',
            'icon' => 'fas fa-layer-group',
            'short_description' => 'Calculate cement and sand quantities needed to plaster a wall to a given thickness.',
            'description' => 'The Plaster Calculator estimates the cement bags and sand volume required to plaster a wall, based on the wall area, plaster thickness and cement:sand ratio, using the standard 1.33 dry-volume factor typical for plaster work.',
            'formula_description' => 'Wet volume = Wall Area × Plaster Thickness. Dry volume = Wet Volume × 1.33. Cement and sand volumes are split according to the cement:sand ratio, and cement volume is converted to bags at 50kg per bag.',
            'meta_title' => 'Plaster Calculator - Cement & Sand for Wall Plastering | Calculator Hub',
            'meta_description' => 'Calculate the cement bags and sand volume needed to plaster your wall based on area, thickness and cement:sand ratio.',
            'faqs' => [
                ['What plaster thickness should I use?', "12mm is standard for internal wall plastering, while external walls often use 15-20mm. Adjust the thickness field to match your project's specification."],
                ['Why does plaster use a different dry volume factor than concrete?', "Plaster mixes typically use a 1.33 dry volume factor rather than concrete's 1.54, reflecting the finer sand-cement mix's different bulking behavior."],
                ['What cement:sand ratio is common for plastering?', 'A 1:6 ratio is common for internal plaster, while 1:4 is common for external plaster. The calculator defaults to 1:6, adjustable to your specification.'],
            ],
        ],
        'excavation_calculator' => [
            'title' => 'Excavation Calculator',
            'icon' => 'fas fa-tractor',
            'short_description' => 'Calculate excavation volume and bulked soil volume for a foundation trench or pit.',
            'description' => 'The Excavation Calculator computes the cut volume of a foundation trench or pit from its length, width and depth, then estimates the bulked (loose) soil volume after excavation using a soil bulking factor, plus an optional cost estimate.',
            'formula_description' => 'Excavation volume = Length × Width × Depth. Bulked soil volume = Excavation Volume × Bulking Factor (typically 1.2).',
            'meta_title' => 'Excavation Calculator - Trench & Pit Volume | Calculator Hub',
            'meta_description' => 'Calculate excavation volume and bulked soil volume for your foundation trench or pit, plus estimated excavation cost.',
            'faqs' => [
                ['Why is the bulked soil volume larger than the excavation volume?', 'Undisturbed soil is compacted; once excavated it loosens and expands, typically by 15-30%, which is why the calculator applies a bulking factor (default 1.2) to estimate the volume of soil to be hauled away.'],
                ['How do I calculate excavation volume for a foundation trench?', "Multiply the trench's length, width and depth in meters to get the volume in cubic meters - the calculator does this automatically from your inputs."],
                ['Can I estimate excavation cost with this calculator?', 'Yes, enter your excavation rate per cubic meter and the calculator will multiply it by the excavation volume to give an estimated cost.'],
            ],
        ],

        'emi_calculator' => [
            'title' => 'EMI Calculator',
            'icon' => 'fas fa-money-check-alt',
            'short_description' => 'Calculate your monthly EMI, total interest and total payment for any loan.',
            'description' => 'The EMI Calculator computes your Equated Monthly Installment for a loan based on the principal amount, annual interest rate and loan tenure, using the standard reducing-balance formula banks use for home, car and personal loans.',
            'formula_description' => 'EMI = P × r × (1+r)ⁿ ÷ ((1+r)ⁿ − 1), where P is the principal, r is the monthly interest rate and n is the number of monthly installments.',
            'meta_title' => 'EMI Calculator - Monthly Loan Installment | Calculator Hub',
            'meta_description' => 'Calculate your monthly EMI, total interest payable and total repayment amount for any loan amount, interest rate and tenure.',
            'faqs' => [
                ['How is EMI calculated?', 'EMI uses the reducing-balance formula: EMI = P×r×(1+r)ⁿ ÷ ((1+r)ⁿ−1), where P is principal, r is the monthly interest rate and n is the number of months. The calculator applies this automatically.'],
                ['Does a longer tenure reduce my EMI?', 'Yes, spreading the loan over more months lowers the monthly installment, but increases the total interest paid over the life of the loan.'],
                ['What happens if the interest rate is 0%?', 'With a 0% rate, EMI simply becomes the principal divided evenly by the number of months, with no interest component.'],
            ],
        ],
        'loan_calculator' => [
            'title' => 'Loan Calculator',
            'icon' => 'fas fa-hand-holding-usd',
            'short_description' => 'Calculate loan installments, total interest and total cost including processing fees.',
            'description' => 'The Loan Calculator is a general-purpose repayment calculator that computes your monthly installment and total interest for any loan, and adds an optional one-time processing fee to reveal the true total cost of borrowing.',
            'formula_description' => 'Monthly installment uses the standard EMI formula based on principal, monthly rate and tenure in months. Total cost = Total Payment + (Principal × Processing Fee %).',
            'meta_title' => 'Loan Calculator - Monthly Payment & Total Cost | Calculator Hub',
            'meta_description' => "Calculate your loan's monthly installment, total interest and true total cost including any processing fees.",
            'faqs' => [
                ["What's the difference between this and the EMI Calculator?", 'This calculator additionally supports a one-time processing fee, letting you see the true total cost of a loan beyond just the interest.'],
                ['How does a processing fee affect total cost?', 'The processing fee is calculated as a percentage of the principal and added on top of the total repayment amount to give the true total cost of the loan.'],
                ['Can I enter the tenure in months instead of years?', 'Yes, this calculator accepts tenure directly in months, which is convenient for shorter-term personal or auto loans.'],
            ],
        ],
        'mortgage_calculator' => [
            'title' => 'Mortgage Calculator',
            'icon' => 'fas fa-home',
            'short_description' => 'Calculate your monthly mortgage payment including property tax and insurance.',
            'description' => 'The Mortgage Calculator estimates your total monthly home loan payment (principal, interest, taxes and insurance) based on home price, down payment, interest rate and loan term, giving a realistic picture of your monthly housing cost.',
            'formula_description' => 'Loan amount = Home Price − Down Payment. Principal & interest use the standard amortization formula; monthly property tax and insurance are added to get the total monthly payment.',
            'meta_title' => 'Mortgage Calculator - Monthly Payment with Taxes & Insurance | Calculator Hub',
            'meta_description' => 'Calculate your total monthly mortgage payment including principal, interest, property tax and home insurance.',
            'faqs' => [
                ['What does PITI mean?', 'PITI stands for Principal, Interest, Taxes and Insurance - the four components that typically make up a full monthly mortgage payment, all included in this calculator\'s result.'],
                ['How does a larger down payment affect my mortgage?', 'A larger down payment reduces the loan amount, which lowers both your monthly principal-and-interest payment and the total interest paid over the loan term.'],
                ['Are property tax and insurance required fields?', "No, they're optional. If you leave them blank, the calculator will show your principal-and-interest payment only."],
            ],
        ],
        'profit_calculator' => [
            'title' => 'Profit Calculator',
            'icon' => 'fas fa-chart-line',
            'short_description' => 'Calculate total profit, profit margin and markup for a batch of goods.',
            'description' => 'The Profit Calculator computes total profit, profit margin percentage and markup percentage for a quantity of goods, given their unit cost price and unit selling price - useful for retailers and product-based businesses.',
            'formula_description' => 'Total Profit = (Selling Price − Cost Price) × Quantity. Profit Margin % = Profit ÷ Total Revenue × 100. Markup % = Profit ÷ Total Cost × 100.',
            'meta_title' => 'Profit Calculator - Total Profit, Margin & Markup | Calculator Hub',
            'meta_description' => 'Calculate total profit, profit margin percentage and markup percentage from your cost price, selling price and quantity sold.',
            'faqs' => [
                ["What's the difference between margin and markup?", 'Margin is profit as a percentage of the selling price (revenue), while markup is profit as a percentage of the cost price. The two numbers are always different for the same sale.'],
                ['How do I calculate total profit for multiple units?', 'Enter the quantity sold along with unit cost and selling price, and the calculator multiplies both totals before computing profit, margin and markup.'],
                ['Why is my profit margin lower than my markup?', 'Margin is calculated against the (larger) selling price while markup is calculated against the (smaller) cost price, so margin percentage is always lower than markup percentage for a profitable sale.'],
            ],
        ],
        'margin_calculator' => [
            'title' => 'Profit Margin Calculator',
            'icon' => 'fas fa-percentage',
            'short_description' => 'Find the selling price needed to hit a target profit margin on a product.',
            'description' => 'The Margin Calculator works backwards from your cost price and a desired profit margin percentage to tell you exactly what selling price you need to charge, along with the resulting profit and markup percentage.',
            'formula_description' => 'Selling Price = Cost Price ÷ (1 − Desired Margin %). Profit = Selling Price − Cost Price. Markup % = Profit ÷ Cost Price × 100.',
            'meta_title' => 'Margin Calculator - Selling Price for Target Profit Margin | Calculator Hub',
            'meta_description' => 'Calculate the selling price needed to achieve your desired profit margin, plus the resulting profit and markup percentage.',
            'faqs' => [
                ['How do I price a product for a 30% margin?', 'Divide your cost price by (1 − 0.30). For example, a $50 cost item needs to sell for about $71.43 to achieve a 30% margin - the calculator computes this instantly for any cost and margin.'],
                ['Can margin be 100% or more?', 'No, margin (as a percentage of selling price) can never reach 100% because the selling price always includes the cost; this calculator caps the input at 99% to avoid division errors.'],
                ['Does this calculator show markup too?', 'Yes, alongside the required selling price, it also reports the equivalent markup percentage so you can compare both pricing perspectives.'],
            ],
        ],
        'gst_calculator' => [
            'title' => 'GST Calculator',
            'icon' => 'fas fa-receipt',
            'short_description' => 'Add or remove GST from an amount, in either exclusive or inclusive mode.',
            'description' => "The GST Calculator computes Goods and Services Tax on any amount. It supports both 'exclusive' mode (adding GST on top of a base price) and 'inclusive' mode (extracting the GST portion already contained in a GST-inclusive price).",
            'formula_description' => 'Exclusive: GST Amount = Amount × Rate ÷ 100; Total = Amount + GST. Inclusive: Base Amount = Amount ÷ (1 + Rate/100); GST Amount = Amount − Base Amount.',
            'meta_title' => 'GST Calculator - Add or Remove GST | Calculator Hub',
            'meta_description' => 'Calculate GST amount and total price, whether adding GST to a base price or extracting GST from a GST-inclusive amount.',
            'faqs' => [
                ['How do I calculate GST on a price?', "Choose 'exclusive' mode, enter the base amount and GST rate, and the calculator adds the GST to give you the total price including tax."],
                ['How do I find the GST already included in a price?', "Choose 'inclusive' mode and enter the GST-inclusive total; the calculator will extract the base amount and the GST portion within it."],
                ['What GST rate should I use?', 'GST rates vary by country and product category - check your local tax authority for the applicable rate for your goods or services.'],
            ],
        ],
        'vat_calculator' => [
            'title' => 'VAT Calculator',
            'icon' => 'fas fa-file-invoice-dollar',
            'short_description' => 'Add or remove VAT from an amount, in either exclusive or inclusive mode.',
            'description' => "The VAT Calculator computes Value Added Tax on any amount, supporting both 'exclusive' mode (adding VAT to a net price) and 'inclusive' mode (extracting the VAT portion already contained within a gross price).",
            'formula_description' => 'Exclusive: VAT Amount = Amount × Rate ÷ 100; Total = Amount + VAT. Inclusive: Base Amount = Amount ÷ (1 + Rate/100); VAT Amount = Amount − Base Amount.',
            'meta_title' => 'VAT Calculator - Add or Remove VAT | Calculator Hub',
            'meta_description' => 'Calculate VAT amount and total price, whether adding VAT to a net price or extracting VAT from a VAT-inclusive amount.',
            'faqs' => [
                ['What is the difference between exclusive and inclusive VAT?', 'Exclusive VAT adds tax on top of a net price to reach the gross price; inclusive VAT works backwards from a gross price to reveal how much of it is tax.'],
                ['What is a typical VAT rate?', 'VAT rates commonly range from 15% to 25% depending on the country - the UK standard rate, for example, is 20%. Adjust the rate field to match your jurisdiction.'],
                ['Can I use this for invoicing?', 'Yes, enter your net amount and VAT rate in exclusive mode to get the exact VAT amount and gross total to show on an invoice.'],
            ],
        ],
        'salary_tax_calculator' => [
            'title' => 'Salary Tax Calculator',
            'icon' => 'fas fa-file-invoice',
            'short_description' => 'Estimate progressive income tax from monthly or annual pay — includes Nepal FY 2082/83 individual slabs.',
            'description' => 'The Salary Tax Calculator estimates income tax using progressive (slab-based) brackets. Choose the Nepal FY 2082/83 individual table (with optional SSF waiver of the 1% SST band) or a generic educational table with optional cess. Enter monthly or annual taxable income.',
            'formula_description' => 'Annual taxable income = (monthly income × 12 or annual income) − deductions. Tax is applied slab-by-slab (Nepal individual: 1%/10%/20%/30%/36%/39% for FY 2082/83; SSF may waive the 1% SST). Optional cess applies only on the generic table.',
            'meta_title' => 'Salary Tax Calculator - Progressive Income Tax Estimate | Calculator Hub',
            'meta_description' => 'Estimate income tax with Nepal FY 2082/83 slabs or a generic progressive table. Supports monthly or annual income and slab-wise breakdown.',
            'faqs' => [
                ["What does 'progressive' tax mean?", 'In a progressive tax system, income is divided into brackets (slabs), and each bracket is taxed at its own rate - so only the portion of income within a higher bracket is taxed at the higher rate, not your entire income.'],
                ['What is the effective tax rate?', "The effective tax rate is your total tax divided by your taxable income, expressed as a percentage - it's always lower than your highest marginal tax bracket rate."],
                ['Are the tax slabs used here specific to my country?', 'Select Nepal FY 2082/83 for Nepal individual slabs, or Generic for an illustrative table. For couple filing and richer Nepal options, use the dedicated Nepal Income Tax calculator.'],
            ],
        ],
        'roi_calculator' => [
            'title' => 'ROI Calculator',
            'icon' => 'fas fa-chart-pie',
            'short_description' => 'Calculate return on investment, net gain and annualized returns.',
            'description' => 'The ROI Calculator computes your total return on investment percentage from an initial investment and its final value, and - when you provide a holding period - also calculates the annualized return for comparing investments held over different time frames.',
            'formula_description' => 'Net Gain = Final Value − Initial Investment. ROI % = Net Gain ÷ Initial Investment × 100. Annualized ROI % = ((Final Value ÷ Initial Investment)^(1/years) − 1) × 100.',
            'meta_title' => 'ROI Calculator - Return on Investment & Annualized Return | Calculator Hub',
            'meta_description' => 'Calculate your total ROI percentage and annualized return from your initial investment, final value and holding period.',
            'faqs' => [
                ['How is ROI calculated?', 'ROI is the net gain (final value minus initial investment) divided by the initial investment, expressed as a percentage. The calculator computes this instantly from your two values.'],
                ['Why does the calculator ask for investment duration?', 'Duration lets the calculator compute an annualized return, which is essential for fairly comparing investments held for different lengths of time.'],
                ['Can ROI be negative?', 'Yes, if the final value is lower than the initial investment, both the net gain and ROI percentage will be negative, indicating a loss.'],
            ],
        ],
        'sip_calculator' => [
            'title' => 'SIP Calculator',
            'icon' => 'fas fa-piggy-bank',
            'short_description' => 'Calculate the future value of your monthly SIP investments.',
            'description' => 'The SIP Calculator projects the maturity value of a Systematic Investment Plan - regular monthly investments - over a chosen period, based on the expected annual rate of return, using the standard annuity-due future value formula.',
            'formula_description' => 'FV = P × [((1+r)ⁿ − 1) ÷ r] × (1+r), where P is the monthly investment, r is the monthly rate of return and n is the number of months. Wealth Gained = Maturity Value − Total Invested.',
            'meta_title' => 'SIP Calculator - Future Value of Monthly Investments | Calculator Hub',
            'meta_description' => 'Calculate the maturity value and wealth gained from your monthly SIP investments based on expected return and investment period.',
            'faqs' => [
                ['How does SIP compound growth work?', 'Each monthly installment earns returns from the day it\'s invested, and those returns compound over the remaining period - so investing consistently over a longer period dramatically increases the final maturity value.'],
                ['What return rate should I assume for SIP?', 'This depends on the underlying investment. Use a conservative, realistic estimate based on the historical performance of your chosen fund type for planning purposes.'],
                ["What is 'wealth gained' in the result?", 'Wealth gained is the maturity value minus the total amount you actually invested - it represents the pure return generated by your SIP over the investment period.'],
            ],
        ],
        'compound_interest_calculator' => [
            'title' => 'Compound Interest Calculator',
            'icon' => 'fas fa-coins',
            'short_description' => 'Calculate compound interest and maturity value for any principal, rate and compounding frequency.',
            'description' => 'The Compound Interest Calculator computes how a principal amount grows over time when interest compounds at a chosen frequency (annually, quarterly, monthly or daily), showing both the final maturity amount and the total interest earned.',
            'formula_description' => 'A = P × (1 + r/n)^(n×t), where P is the principal, r is the annual interest rate, n is the compounding frequency per year, and t is the time in years. Interest Earned = A − P.',
            'meta_title' => 'Compound Interest Calculator - Maturity Amount & Interest Earned | Calculator Hub',
            'meta_description' => 'Calculate compound interest and maturity value for any principal, interest rate, time period and compounding frequency.',
            'faqs' => [
                ['How does compounding frequency affect returns?', 'More frequent compounding (e.g. monthly or daily versus annually) generates slightly higher returns for the same nominal interest rate, since interest is calculated and added to the principal more often.'],
                ["What's the difference between simple and compound interest?", 'Simple interest is calculated only on the original principal, while compound interest is calculated on the principal plus all previously earned interest - leading to exponential rather than linear growth.'],
                ['How long does it take to double my money?', "As a rule of thumb, divide 72 by your annual interest rate to estimate the years needed to double your investment (the 'Rule of 72') - or use this calculator with different time periods to find the exact answer."],
            ],
        ],

        'bmi_calculator' => [
            'title' => 'BMI Calculator',
            'icon' => 'fas fa-weight',
            'short_description' => 'Calculate your Body Mass Index and see which weight category you fall into.',
            'description' => 'The BMI Calculator computes your Body Mass Index from your height and weight, supporting both metric (kg/cm) and imperial (lb/in) units, and tells you which standard weight category you fall into, along with a healthy weight range for your height.',
            'formula_description' => 'BMI = Weight (kg) ÷ Height (m)². Values are converted to metric first if imperial units are selected, then classified against standard BMI category thresholds.',
            'meta_title' => 'BMI Calculator - Body Mass Index & Healthy Weight Range | Calculator Hub',
            'meta_description' => 'Calculate your BMI and healthy weight range instantly from your height and weight, in metric or imperial units.',
            'faqs' => [
                ['What is a healthy BMI range?', 'A BMI between 18.5 and 24.9 is generally classified as a normal, healthy weight range for most adults.'],
                ['Is BMI accurate for everyone?', "BMI is a useful general screening tool but doesn't account for muscle mass, bone density or body composition. Consult a healthcare provider for a personalized assessment."],
                ['Can I use imperial units like pounds and inches?', "Yes, select 'Imperial (lb/in)' as the unit system and enter your weight in pounds and height in inches - the calculator converts these automatically."],
            ],
        ],
        'bmr_calculator' => [
            'title' => 'BMR Calculator',
            'icon' => 'fas fa-fire',
            'short_description' => 'Calculate your Basal Metabolic Rate and daily calorie needs (TDEE).',
            'description' => 'The BMR Calculator computes your Basal Metabolic Rate - the calories your body burns at rest - using the Mifflin-St Jeor equation, then multiplies it by an activity factor to estimate your Total Daily Energy Expenditure (TDEE).',
            'formula_description' => 'Men: BMR = 10×weight(kg) + 6.25×height(cm) − 5×age + 5. Women: BMR = 10×weight(kg) + 6.25×height(cm) − 5×age − 161. TDEE = BMR × Activity Multiplier.',
            'meta_title' => 'BMR Calculator - Basal Metabolic Rate & TDEE | Calculator Hub',
            'meta_description' => 'Calculate your BMR and Total Daily Energy Expenditure (TDEE) using the Mifflin-St Jeor equation and your activity level.',
            'faqs' => [
                ['What is BMR?', 'BMR (Basal Metabolic Rate) is the number of calories your body needs to maintain basic life functions - breathing, circulation, cell production - while completely at rest.'],
                ["What's the difference between BMR and TDEE?", 'BMR is calories burned at rest, while TDEE adds your daily activity level on top, giving a more realistic estimate of total daily calorie burn.'],
                ['Which formula does this calculator use?', 'It uses the Mifflin-St Jeor equation, which is widely regarded by nutrition professionals as more accurate than older formulas like Harris-Benedict.'],
            ],
        ],
        'body_fat_calculator' => [
            'title' => 'Body Fat Calculator',
            'icon' => 'fas fa-user',
            'short_description' => 'Estimate your body fat percentage using the U.S. Navy circumference method.',
            'description' => 'The Body Fat Calculator estimates your body fat percentage using the U.S. Navy circumference method, which uses waist, neck (and for women, hip) measurements alongside height - no calipers or scales required.',
            'formula_description' => 'Men: BFP = 495 ÷ (1.0324 − 0.19077×log₁₀(waist−neck) + 0.15456×log₁₀(height)) − 450. Women: BFP = 495 ÷ (1.29579 − 0.35004×log₁₀(waist+hip−neck) + 0.221×log₁₀(height)) − 450.',
            'meta_title' => 'Body Fat Calculator - U.S. Navy Method | Calculator Hub',
            'meta_description' => 'Estimate your body fat percentage using the U.S. Navy circumference method from waist, neck, hip and height measurements.',
            'faqs' => [
                ['How accurate is the U.S. Navy method?', 'It\'s a well-validated estimation method with a typical margin of error of around 3-4%, making it a convenient alternative to skinfold calipers or DEXA scans for most people.'],
                ['Why does the formula differ for men and women?', "Men and women tend to store fat differently, so the Navy method uses different circumference measurements and coefficients for each - women's calculations additionally factor in hip circumference."],
                ['What measurements do I need?', 'You need waist and neck circumference for men, plus hip circumference for women, all measured in centimeters, along with your height.'],
            ],
        ],
        'calorie_calculator' => [
            'title' => 'Calorie Calculator',
            'icon' => 'fas fa-apple-alt',
            'short_description' => 'Calculate your daily calorie needs to lose, maintain or gain weight.',
            'description' => 'The Calorie Calculator estimates your daily calorie needs based on the Mifflin-St Jeor BMR formula and your activity level, then adjusts the result for your specific goal - losing, maintaining or gaining weight - using a standard ±500 kcal/day adjustment.',
            'formula_description' => 'BMR is calculated via the Mifflin-St Jeor equation, multiplied by an activity factor to get maintenance calories. Goal calories = Maintenance Calories ± 500 kcal.',
            'meta_title' => 'Calorie Calculator - Daily Calorie Needs for Your Goal | Calculator Hub',
            'meta_description' => 'Calculate your daily calorie needs to lose, maintain or gain weight, based on your BMR and activity level.',
            'faqs' => [
                ['How many calories should I eat to lose weight?', "A deficit of around 500 kcal/day below your maintenance calories typically leads to about 0.45kg (1lb) of weight loss per week - select 'Lose Weight' as your goal to see this figure calculated for you."],
                ['What activity level should I select?', "Choose the option that best matches your typical week: 'Sedentary' for little/no exercise, up to 'Extremely active' for hard daily exercise or a physical job."],
                ['Why is there a minimum calorie floor of 1200?', 'The calculator caps goal calories at a minimum of 1200 kcal/day, as going below this is generally not recommended without medical supervision.'],
            ],
        ],
        'water_intake_calculator' => [
            'title' => 'Water Intake Calculator',
            'icon' => 'fas fa-tint',
            'short_description' => 'Calculate your recommended daily water intake based on weight, exercise and climate.',
            'description' => 'The Water Intake Calculator estimates how much water you should drink daily based on your body weight, plus additional intake for exercise duration and hot/humid climates, giving a result in both liters and standard glasses.',
            'formula_description' => 'Base Intake = Weight (kg) × 0.033 L/kg. Exercise Intake = (Exercise Minutes ÷ 30) × 0.35L. Climate Intake = +0.5L if hot/humid. Total = Base + Exercise + Climate.',
            'meta_title' => 'Water Intake Calculator - Daily Hydration Needs | Calculator Hub',
            'meta_description' => 'Calculate your recommended daily water intake in liters and glasses, based on your weight, exercise routine and climate.',
            'faqs' => [
                ['How much water should I drink per day?', 'A common baseline is about 33ml per kilogram of body weight - for a 70kg person, that\'s roughly 2.3 liters, before accounting for exercise or hot weather.'],
                ['Does exercise increase my water needs?', 'Yes, the calculator adds roughly 350ml of extra water intake for every 30 minutes of exercise to replace fluids lost through sweat.'],
                ['How many glasses of water is that?', 'The calculator converts your total liters into standard 250ml glasses, giving you an easy practical target to track throughout the day.'],
            ],
        ],

        'gpa_calculator' => [
            'title' => 'GPA Calculator',
            'icon' => 'fas fa-graduation-cap',
            'short_description' => 'Calculate your semester GPA from course credits and grades.',
            'description' => "The GPA Calculator computes your Grade Point Average for a semester by weighting each course's grade points by its credit hours. You can enter a letter grade (automatically mapped to the 4.0 scale) or a precise numeric grade point for each course.",
            'formula_description' => 'GPA = Σ(Credits × Grade Points) ÷ Σ(Credits), calculated across every course you enter.',
            'meta_title' => 'GPA Calculator - Semester Grade Point Average | Calculator Hub',
            'meta_description' => 'Calculate your semester GPA from your course credit hours and letter grades or grade points, on the standard 4.0 scale.',
            'faqs' => [
                ['How is GPA calculated?', "GPA is the credit-weighted average of your grade points: multiply each course's grade points by its credit hours, sum these across all courses, then divide by the total credit hours."],
                ['What is the grade point scale used?', 'The calculator uses the standard 4.0 scale where A/A+ = 4.0, A- = 3.7, B+ = 3.3, B = 3.0, and so on down to F = 0.0.'],
                ['Can I enter an exact grade point instead of a letter grade?', "Yes, if your institution uses a non-standard scale, enter the exact grade points for a course directly and the calculator will use that value instead of the letter-grade mapping."],
            ],
        ],
        'percentage_calculator' => [
            'title' => 'Percentage Calculator',
            'icon' => 'fas fa-percent',
            'short_description' => 'Calculate percentages, percentage of a value, or percentage change between two numbers.',
            'description' => 'The Percentage Calculator handles three common percentage calculations in one tool: finding X% of Y, determining what percent X is of Y, and computing the percentage change (increase or decrease) from one value to another.',
            'formula_description' => 'X% of Y = Y × (X/100). X is what % of Y = (X ÷ Y) × 100. Percentage change from X to Y = ((Y − X) ÷ |X|) × 100.',
            'meta_title' => 'Percentage Calculator - Percent Of, Percent Change | Calculator Hub',
            'meta_description' => 'Calculate X% of Y, what percent one number is of another, or the percentage increase/decrease between two values.',
            'faqs' => [
                ['How do I calculate what percent one number is of another?', "Select the 'X is what percent of Y' mode, enter both values, and the calculator divides X by Y and multiplies by 100 to give the percentage."],
                ['How do I calculate percentage increase or decrease?', "Select 'Percentage change from X to Y' - the calculator computes ((Y−X)÷X)×100, giving a positive result for an increase and a negative result for a decrease."],
                ["What does '20% of 200' mean and how is it calculated?", "It means 20 percent of the value 200, calculated as 200 × (20/100) = 40. Select the 'X% of Y' mode and enter 20 and 200 to see this instantly."],
            ],
        ],
        'cgpa_calculator' => [
            'title' => 'CGPA Calculator',
            'icon' => 'fas fa-user-graduate',
            'short_description' => 'Calculate your cumulative GPA across multiple semesters.',
            'description' => 'The CGPA Calculator aggregates your GPA and credit hours across multiple semesters into a single cumulative GPA (CGPA), and also converts the result into an equivalent percentage using your institution\'s grading scale.',
            'formula_description' => 'CGPA = Σ(Semester GPA × Semester Credits) ÷ Σ(Semester Credits). Percentage Equivalent = (CGPA ÷ Scale) × 100.',
            'meta_title' => 'CGPA Calculator - Cumulative Grade Point Average | Calculator Hub',
            'meta_description' => 'Calculate your cumulative GPA (CGPA) across multiple semesters and convert it to a percentage equivalent.',
            'faqs' => [
                ['How is CGPA different from GPA?', 'GPA reflects a single semester\'s performance, while CGPA is the credit-weighted average of your GPA across all semesters completed so far, giving an overall academic performance measure.'],
                ['How do I convert CGPA to a percentage?', "Divide your CGPA by your institution's grading scale (commonly 10) and multiply by 100. This calculator does that conversion automatically using the scale you specify."],
                ['Does a low GPA in one semester significantly affect CGPA?', 'It depends on that semester\'s credit hours relative to your total - a semester with more credits has a proportionally larger effect on your overall CGPA than a lighter semester.'],
            ],
        ],

        'discount_calculator' => [
            'title' => 'Discount Calculator',
            'icon' => 'fas fa-tags',
            'short_description' => 'Calculate the final price and savings after one or two successive discounts.',
            'description' => 'The Discount Calculator computes the final price after applying one or two successive percentage discounts to an original price, and reports the total savings and the single effective discount percentage that produces the same result.',
            'formula_description' => 'Price after 1st discount = Original Price × (1 − Discount1/100). Final Price = that value × (1 − Discount2/100). Effective Discount % = Total Savings ÷ Original Price × 100.',
            'meta_title' => 'Discount Calculator - Final Price After Discount | Calculator Hub',
            'meta_description' => 'Calculate the final price and total savings after applying one or two successive percentage discounts to an original price.',
            'faqs' => [
                ['How do successive discounts work?', "Successive discounts are applied one after another to the already-discounted price, not added together - so a 20% discount followed by a 10% discount is not the same as a flat 30% discount."],
                ["What is the 'effective discount percentage'?", "It's the single discount rate that would produce the same final price as your two successive discounts combined - useful for comparing stacked deals to a simple one-time discount."],
                ['Can I calculate just one discount?', 'Yes, leave the additional discount field at 0% and the calculator will apply only the first discount percentage.'],
            ],
        ],
        'commission_calculator' => [
            'title' => 'Commission Calculator',
            'icon' => 'fas fa-handshake',
            'short_description' => 'Calculate sales commission earned and total earnings including base salary.',
            'description' => 'The Commission Calculator computes the commission earned on a sales amount at a given commission rate, and adds it to an optional base salary to show total earnings - useful for sales teams and commission-based roles.',
            'formula_description' => 'Commission Earned = Sales Amount × Commission Rate ÷ 100. Total Earnings = Base Salary + Commission Earned.',
            'meta_title' => 'Commission Calculator - Sales Commission & Total Earnings | Calculator Hub',
            'meta_description' => 'Calculate sales commission earned from your sales amount and commission rate, plus total earnings including base salary.',
            'faqs' => [
                ['How is sales commission calculated?', 'Commission is simply the sales amount multiplied by the commission rate percentage - for example, $10,000 in sales at a 5% rate earns $500 in commission.'],
                ['Does this calculator include base salary?', 'Yes, if your role includes a base salary, enter it and the calculator will add it to your commission to show total earnings for the period.'],
                ['Can I use this for tiered commission structures?', 'This calculator uses a single flat commission rate. For tiered structures, calculate each tier\'s commission separately and sum the results.'],
            ],
        ],
        'payroll_calculator' => [
            'title' => 'Payroll Calculator',
            'icon' => 'fas fa-money-check',
            'short_description' => 'Calculate gross salary, tax and net take-home pay including overtime.',
            'description' => "The Payroll Calculator computes an employee's gross salary from basic pay, allowances and overtime, then deducts tax and other deductions to arrive at the final net take-home pay.",
            'formula_description' => 'Overtime Pay = Overtime Hours × Overtime Rate. Gross Salary = Basic Salary + Allowances + Overtime Pay. Net Salary = Gross Salary − Tax Amount − Other Deductions.',
            'meta_title' => 'Payroll Calculator - Gross Salary & Net Take-Home Pay | Calculator Hub',
            'meta_description' => 'Calculate gross salary, tax deducted and net take-home pay from basic salary, allowances, overtime and deductions.',
            'faqs' => [
                ['How is gross salary calculated?', 'Gross salary is the sum of basic salary, any allowances, and overtime pay (overtime hours multiplied by the overtime rate) - before any deductions are applied.'],
                ['What deductions does this calculator account for?', 'It applies a percentage-based tax deduction plus any other fixed deductions you specify (such as insurance or loan repayments) to arrive at net salary.'],
                ['Can I use this for hourly employees?', 'Yes, enter the equivalent basic salary for the pay period and use the overtime fields for any hours worked beyond standard hours.'],
            ],
        ],

        'length_converter' => [
            'title' => 'Length Converter',
            'icon' => 'fas fa-ruler',
            'short_description' => 'Convert length between millimeters, centimeters, meters, kilometers, inches, feet, yards and miles.',
            'description' => 'The Length Converter instantly converts a length value between metric units (mm, cm, m, km) and imperial units (in, ft, yd, mile) by normalizing to meters as a common base unit.',
            'formula_description' => 'Value in meters = Input Value × Conversion Factor (from-unit to meters). Converted Value = Value in Meters ÷ Conversion Factor (to-unit).',
            'meta_title' => 'Length Converter - mm, cm, m, km, in, ft, yd, mile | Calculator Hub',
            'meta_description' => 'Convert length instantly between millimeters, centimeters, meters, kilometers, inches, feet, yards and miles.',
            'faqs' => [
                ['How many feet are in a meter?', "1 meter equals approximately 3.281 feet. Enter 1 in the value field, select 'm' as the from-unit and 'ft' as the to-unit to see this conversion instantly."],
                ['What units does this converter support?', 'It supports millimeters, centimeters, meters, kilometers, inches, feet, yards and miles - covering the most commonly used metric and imperial length units.'],
                ['How accurate is the conversion?', 'Conversions use precise standard conversion factors (e.g. 1 inch = 0.0254m exactly) and results are shown to 6 decimal places for maximum accuracy.'],
            ],
        ],
        'area_converter' => [
            'title' => 'Area Converter',
            'icon' => 'fas fa-ruler-combined',
            'short_description' => 'Convert area between square meters, square feet, acres, hectares and more.',
            'description' => 'The Area Converter instantly converts an area value between metric units (sq mm, sq cm, sq m, hectare, sq km) and imperial/other units (sq ft, sq yd, acre, sq mile) by normalizing to square meters as a common base.',
            'formula_description' => 'Value in sq meters = Input Value × Conversion Factor (from-unit to sq meters). Converted Value = Value in sq Meters ÷ Conversion Factor (to-unit).',
            'meta_title' => 'Area Converter - sq m, sq ft, acre, hectare | Calculator Hub',
            'meta_description' => 'Convert area instantly between square meters, square feet, acres, hectares, square kilometers and more.',
            'faqs' => [
                ['How many square feet are in an acre?', "1 acre equals approximately 43,560 square feet. Select 'acre' as the from-unit and 'sq_ft' as the to-unit with a value of 1 to confirm."],
                ['How many square meters are in a hectare?', '1 hectare equals exactly 10,000 square meters - a unit commonly used for measuring land area in agriculture and real estate.'],
                ['What units are supported?', 'The converter supports square millimeters, square centimeters, square meters, hectares, square kilometers, square feet, square yards, acres and square miles.'],
            ],
        ],
        'volume_converter' => [
            'title' => 'Volume Converter',
            'icon' => 'fas fa-flask',
            'short_description' => 'Convert volume between liters, milliliters, cubic meters, gallons and more.',
            'description' => 'The Volume Converter instantly converts a volume value between metric units (ml, l, m³, cm³) and imperial/US units (US/UK gallons, US quarts, cubic feet, cubic inches) by normalizing to liters as a common base unit.',
            'formula_description' => 'Value in liters = Input Value × Conversion Factor (from-unit to liters). Converted Value = Value in Liters ÷ Conversion Factor (to-unit).',
            'meta_title' => 'Volume Converter - Liters, Gallons, m³, ft³ | Calculator Hub',
            'meta_description' => 'Convert volume instantly between liters, milliliters, cubic meters, US/UK gallons, cubic feet and more.',
            'faqs' => [
                ['How many liters are in a US gallon?', '1 US gallon equals approximately 3.785 liters. Note this differs from the UK (imperial) gallon, which equals about 4.546 liters - select the correct one for your region.'],
                ['What\'s the difference between US and UK gallons?', "US gallons and UK (imperial) gallons are different units with different volumes; the converter treats them as separate units so you always get the correct conversion."],
                ['How do I convert cubic meters to liters?', "1 cubic meter equals exactly 1,000 liters. Select 'm3' as the from-unit and 'l' as the to-unit to convert instantly."],
            ],
        ],
        'weight_converter' => [
            'title' => 'Weight Converter',
            'icon' => 'fas fa-balance-scale',
            'short_description' => 'Convert weight between kilograms, grams, pounds, ounces, tons and stone.',
            'description' => 'The Weight Converter instantly converts a weight/mass value between metric units (mg, g, kg, metric ton) and imperial units (lb, oz, stone) by normalizing to kilograms as a common base unit.',
            'formula_description' => 'Value in kg = Input Value × Conversion Factor (from-unit to kg). Converted Value = Value in kg ÷ Conversion Factor (to-unit).',
            'meta_title' => 'Weight Converter - kg, lb, oz, stone, tons | Calculator Hub',
            'meta_description' => 'Convert weight instantly between kilograms, grams, pounds, ounces, metric tons and stone.',
            'faqs' => [
                ['How many pounds are in a kilogram?', "1 kilogram equals approximately 2.205 pounds. Enter 1 as the value, 'kg' as the from-unit and 'lb' as the to-unit to verify."],
                ["What is a 'stone' in weight measurement?", 'A stone is a traditional British unit of weight equal to 14 pounds (about 6.35kg), still commonly used in the UK and Ireland for body weight.'],
                ['How many kilograms are in a metric ton?', "1 metric ton equals exactly 1,000 kilograms - this converter clearly labels it 'ton_metric' to distinguish it from the US (short) ton."],
            ],
        ],
        'temperature_converter' => [
            'title' => 'Temperature Converter',
            'icon' => 'fas fa-thermometer-half',
            'short_description' => 'Convert temperature between Celsius, Fahrenheit and Kelvin.',
            'description' => "The Temperature Converter converts a temperature value between Celsius, Fahrenheit and Kelvin scales, correctly handling each scale's different zero-point offset rather than a simple multiplicative conversion.",
            'formula_description' => 'Fahrenheit to Celsius: (F−32) × 5/9. Kelvin to Celsius: K − 273.15. Celsius to Fahrenheit: (C × 9/5) + 32. Celsius to Kelvin: C + 273.15.',
            'meta_title' => 'Temperature Converter - Celsius, Fahrenheit, Kelvin | Calculator Hub',
            'meta_description' => 'Convert temperature instantly between Celsius, Fahrenheit and Kelvin scales.',
            'faqs' => [
                ['How do I convert Celsius to Fahrenheit?', 'Multiply the Celsius value by 9/5 and add 32. For example, 25°C converts to 77°F - the calculator applies this formula automatically.'],
                ["Why can't I just multiply to convert temperature?", "Unlike length or weight, temperature scales have different zero points, so conversions require adding or subtracting an offset in addition to scaling."],
                ['What is absolute zero in each scale?', '0 Kelvin equals -273.15°C and -459.67°F - the theoretical point at which all thermal motion stops.'],
            ],
        ],
        'speed_converter' => [
            'title' => 'Speed Converter',
            'icon' => 'fas fa-tachometer-alt',
            'short_description' => 'Convert speed between km/h, mph, m/s, knots and feet per second.',
            'description' => 'The Speed Converter instantly converts a speed value between common units - meters per second, kilometers per hour, miles per hour, knots and feet per second - by normalizing to meters per second as a common base unit.',
            'formula_description' => 'Value in m/s = Input Value × Conversion Factor (from-unit to m/s). Converted Value = Value in m/s ÷ Conversion Factor (to-unit).',
            'meta_title' => 'Speed Converter - km/h, mph, m/s, knots | Calculator Hub',
            'meta_description' => 'Convert speed instantly between kilometers per hour, miles per hour, meters per second, knots and feet per second.',
            'faqs' => [
                ['How do I convert km/h to mph?', "Multiply the km/h value by approximately 0.6214 to get mph - or simply enter your value with 'kmph' and 'mph' selected in the converter for an instant, precise result."],
                ['What is a knot?', 'A knot is a unit of speed equal to one nautical mile per hour (about 1.852 km/h), commonly used in aviation and maritime navigation.'],
                ['What units does this converter support?', 'It supports meters per second, kilometers per hour, miles per hour, knots and feet per second.'],
            ],
        ],

        'age_calculator' => [
            'title' => 'Age Calculator',
            'icon' => 'fas fa-birthday-cake',
            'short_description' => 'Calculate your exact age in years, months and days, plus countdown to your next birthday.',
            'description' => 'The Age Calculator computes your exact age in years, months and days between a birth date and a reference date (defaulting to today), along with total elapsed days, months, weeks, and a countdown to your next birthday.',
            'formula_description' => "Age is calculated as the calendar difference between the birth date and reference date, broken into years, months and days. The next birthday is found by advancing the birth date's month/day to the current or next year.",
            'meta_title' => 'Age Calculator - Exact Age in Years, Months & Days | Calculator Hub',
            'meta_description' => 'Calculate your exact age in years, months and days from your date of birth, plus days remaining until your next birthday.',
            'faqs' => [
                ['How do I calculate my exact age?', 'Enter your date of birth and the calculator will show your exact age in years, months and days as of today, or as of any custom reference date you specify.'],
                ['Can I calculate age as of a future or past date?', "Yes, use the optional 'As of Date' field to calculate age relative to any specific date, not just today."],
                ['Does the calculator show my next birthday?', 'Yes, it shows the exact number of days remaining until your next birthday, calculated from the reference date.'],
            ],
        ],
        'date_difference_calculator' => [
            'title' => 'Date Difference Calculator',
            'icon' => 'fas fa-calendar-alt',
            'short_description' => 'Calculate the number of days, weeks, months and years between two dates.',
            'description' => 'The Date Difference Calculator computes the exact duration between two dates, breaking it down into years, months and days, plus total days, total weeks, and the number of business days (Monday-Friday) between them.',
            'formula_description' => 'The two dates are ordered chronologically (swapped if necessary), then the calendar difference is computed in years/months/days, alongside a straight day-count for total days and business days.',
            'meta_title' => 'Date Difference Calculator - Days Between Two Dates | Calculator Hub',
            'meta_description' => 'Calculate the exact number of years, months, days, weeks and business days between any two dates.',
            'faqs' => [
                ['How many days are between two dates?', 'Enter both dates and the calculator instantly shows the total number of days between them, along with the equivalent in years, months, weeks and business days.'],
                ['What counts as a business day?', 'Business days are counted as Monday through Friday, excluding Saturdays and Sundays - public holidays are not automatically excluded.'],
                ['Does it matter which date I enter first?', 'No, the calculator automatically detects if the end date is earlier than the start date and swaps them, so the result is always a positive duration.'],
            ],
        ],
        'date_converter_calculator' => [
            'title' => 'Date Converter (AD ↔ BS)',
            'icon' => 'fas fa-exchange-alt',
            'short_description' => 'Convert English (AD / Gregorian) dates to Nepali Bikram Sambat (BS) and BS back to AD.',
            'description' => 'The Date Converter converts between the Gregorian calendar (AD) and Nepal’s official Bikram Sambat (BS) calendar in both directions. Use it for forms, documents, festivals, and official date translations. Supported range: AD 1944–2033 / BS 2000–2089.',
            'formula_description' => 'Conversion uses a validated Bikram Sambat month-length lookup table (not a fixed year offset). AD→BS accumulates Gregorian days from a known epoch and maps into BS year/month/day; BS→AD reverses that mapping.',
            'meta_title' => 'AD to BS Date Converter - Nepali Bikram Sambat Calendar | Calculator Hub',
            'meta_description' => 'Convert AD (English) dates to BS (Bikram Sambat) and BS to AD instantly. Free Nepali date converter for Nepal.',
            'faqs' => [
                ['How do I convert AD to BS?', 'Choose “AD → BS”, enter the Gregorian date, and calculate. You will get the matching Bikram Sambat year, month and day with weekday.'],
                ['How do I convert BS to AD?', 'Choose “BS → AD”, enter the BS year, Nepali month and day, then calculate to get the Gregorian date.'],
                ['What date range is supported?', 'This converter supports AD dates from 1944 to 2033 and BS dates from 2000 to 2089, based on the embedded calendar table.'],
            ],
        ],
        'time_calculator' => [
            'title' => 'Time Duration Calculator',
            'icon' => 'fas fa-clock',
            'short_description' => 'Calculate the difference between two times, or add/subtract time from a start time.',
            'description' => 'The Time Calculator supports two modes: calculating the elapsed duration between a start and end time (automatically rolling over midnight if needed), or adding/subtracting a number of hours and minutes from a given start time.',
            'formula_description' => 'Difference mode: Total Minutes = End Time − Start Time (adding 24 hours if the end time is earlier than the start time). Add mode: Result Time = Start Time + (Hours × 60 + Minutes).',
            'meta_title' => 'Time Duration Calculator - Time Difference & Add Time | Calculator Hub',
            'meta_description' => 'Calculate the time difference between two times, or add/subtract hours and minutes from a start time.',
            'faqs' => [
                ['How do I calculate hours worked between two times?', "Select 'Time Difference' mode, enter your start and end time in HH:MM format, and the calculator shows the total elapsed hours and minutes."],
                ['What happens if my end time is before my start time?', 'The calculator assumes the end time falls on the next day and automatically rolls over midnight, still giving you the correct elapsed duration.'],
                ['Can I add or subtract time from a specific time?', "Yes, select 'Add/Subtract Time' mode, enter a start time and the number of hours/minutes to add (use negative numbers to subtract), and the calculator gives you the resulting time."],
            ],
        ],

        'beam_calculator' => [
            'title' => 'Beam Load Calculator',
            'icon' => 'fas fa-grip-lines-vertical',
            'short_description' => 'Calculate bending moment, shear force and deflection for a simply-supported beam.',
            'description' => 'The Beam Calculator analyzes a simply-supported beam under a uniformly distributed load (UDL), computing the maximum bending moment, maximum shear force, and maximum deflection - key checks in structural beam design.',
            'formula_description' => 'Max Bending Moment M = wL²/8. Max Shear Force V = wL/2. Max Deflection δ = 5wL⁴ ÷ (384EI), where w is the UDL, L is span length, E is the modulus of elasticity and I is the moment of inertia.',
            'meta_title' => 'Beam Calculator - Bending Moment, Shear & Deflection | Calculator Hub',
            'meta_description' => 'Calculate maximum bending moment, shear force and deflection for a simply-supported beam under uniform load.',
            'faqs' => [
                ['What load case does this calculator assume?', 'It assumes a simply-supported beam under a uniformly distributed load (UDL) across its full span - the most common load case for preliminary beam sizing.'],
                ['What units should I use for modulus of elasticity and moment of inertia?', 'Modulus of elasticity should be in N/mm² (MPa) and moment of inertia in mm⁴; the span length is entered in meters and converted internally for the deflection calculation.'],
                ['Is this suitable for final structural design?', 'This calculator provides a quick preliminary estimate for simply-supported UDL cases. Final structural design should always be verified by a qualified structural engineer against the applicable building code.'],
            ],
        ],
        'slab_calculator' => [
            'title' => 'Slab Calculator',
            'icon' => 'fas fa-layer-group',
            'short_description' => 'Estimate concrete volume, steel weight and bending moment for an RCC slab.',
            'description' => 'The Slab Calculator estimates the concrete volume and reinforcement steel weight for a reinforced concrete slab based on its dimensions and a typical steel reinforcement percentage, and also reports the maximum one-way bending moment under a design load.',
            'formula_description' => 'Concrete Volume = Length × Width × Thickness. Steel Weight = Concrete Volume × Steel % × 7850 kg/m³. Max Bending Moment = (UDL × Shorter Span²) ÷ 8.',
            'meta_title' => 'Slab Calculator - Concrete Volume & Steel Weight | Calculator Hub',
            'meta_description' => 'Estimate concrete volume, reinforcement steel weight and maximum bending moment for an RCC slab.',
            'faqs' => [
                ['What steel percentage is typical for slabs?', '0.7% to 1% of the concrete volume is a common range for slab reinforcement, though the exact figure depends on the structural design and span - the calculator defaults to 0.8%.'],
                ['How is steel weight estimated from a percentage?', 'The calculator multiplies concrete volume by the steel percentage to get steel volume, then multiplies by steel\'s density (7850 kg/m³) to get weight - a quick estimate, not a substitute for detailed rebar design.'],
                ['What span does the bending moment calculation use?', 'It uses the shorter of the slab\'s length and width, consistent with one-way slab design where the shorter span typically governs the main reinforcement direction.'],
            ],
        ],
        'column_calculator' => [
            'title' => 'Column Load Calculator',
            'icon' => 'fas fa-columns',
            'short_description' => 'Calculate the axial load capacity of a reinforced concrete column.',
            'description' => 'The Column Calculator estimates the axial load-carrying capacity of a short, axially loaded RCC column using a simplified IS 456-style approach, based on the column\'s cross-section, concrete grade, steel grade and reinforcement percentage.',
            'formula_description' => 'Pu = 0.4 × fck × Ac + 0.67 × fy × Asc, where fck is the concrete grade, fy is the steel grade, Ac is the net concrete area and Asc is the longitudinal steel area.',
            'meta_title' => 'Column Calculator - Axial Load Capacity | Calculator Hub',
            'meta_description' => 'Calculate the axial load-carrying capacity of a reinforced concrete column from its size, concrete grade, steel grade and reinforcement.',
            'faqs' => [
                ["What does 'fck' and 'fy' mean?", 'fck is the characteristic compressive strength of concrete (e.g. M20 = 20 N/mm²), and fy is the yield strength of the reinforcement steel (e.g. Fe415 = 415 N/mm²).'],
                ['What steel percentage should I use for a column?', 'Codes typically require longitudinal steel between 0.8% and 6% of the gross cross-sectional area; 1-2% is common for typical building columns.'],
                ['Is this formula suitable for slender columns?', 'No, this simplified formula applies to short, axially loaded columns only. Slender columns require additional buckling checks that this calculator does not perform.'],
            ],
        ],
        'footing_calculator' => [
            'title' => 'Footing Calculator',
            'icon' => 'fas fa-square',
            'short_description' => 'Size an isolated square footing based on column load and soil bearing capacity.',
            'description' => "The Footing Calculator determines the required base area for an isolated square footing from the column's axial load and the soil's safe bearing capacity, and - if you specify a proposed footing size - checks whether that size is adequate.",
            'formula_description' => 'Required Area = Column Load ÷ Safe Bearing Capacity. Square Footing Side = √(Required Area). If a proposed size is given, Base Pressure = Load ÷ Provided Area, checked against the SBC.',
            'meta_title' => 'Footing Calculator - Isolated Footing Size | Calculator Hub',
            'meta_description' => 'Calculate the required base area and side length for an isolated square footing from column load and soil bearing capacity.',
            'faqs' => [
                ['How is safe bearing capacity (SBC) determined?', 'SBC is determined by a geotechnical soil investigation for your specific site - it should never be assumed, as it varies widely with soil type and depth.'],
                ["What does 'required area' mean?", "It's the minimum footing base area needed so the pressure the footing transfers to the soil does not exceed the soil's safe bearing capacity, calculated as load divided by SBC."],
                ['How do I check if my proposed footing size is safe?', "Enter your proposed footing length and width, and the calculator computes the actual base pressure and compares it to the safe bearing capacity, reporting 'Safe' or 'Unsafe'."],
            ],
        ],
    ];

    protected int $categoriesCreated = 0;

    protected int $calculatorsCreated = 0;

    protected int $faqsCreated = 0;

    protected int $examplesCreated = 0;

    public function run(CalculatorRegistry $registry): void
    {
        DB::transaction(function () use ($registry): void {
            $categoryIds = $this->seedCategories();
            $this->seedCalculators($registry, $categoryIds);
        });

        $this->command?->info(sprintf(
            'CalculatorSeeder: %d categories, %d calculators, %d FAQs, %d examples seeded.',
            $this->categoriesCreated,
            $this->calculatorsCreated,
            $this->faqsCreated,
            $this->examplesCreated
        ));
    }

    /**
     * @return array<string, int> Category slug => id
     */
    protected function seedCategories(): array
    {
        $ids = [];

        foreach (self::CATEGORIES as $category) {
            $model = CalculatorCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'icon' => CalculatorIconMap::forCategory($category['slug']),
                    'description' => $category['description'],
                    'meta_title' => $category['meta_title'],
                    'meta_description' => $category['meta_description'],
                    'sort_order' => $category['sort_order'],
                    'is_active' => true,
                ]
            );

            $ids[$category['slug']] = $model->id;
            $this->categoriesCreated++;
        }

        return $ids;
    }

    /**
     * @param  array<string, int>  $categoryIds
     */
    protected function seedCalculators(CalculatorRegistry $registry, array $categoryIds): void
    {
        $sortOrder = 0;

        foreach ($registry->all() as $key => $handler) {
            $sortOrder++;

            $meta = self::CALCULATOR_META[$key] ?? $this->fallbackMeta($key);
            $categorySlug = $this->categoryMap()[$key] ?? 'daily-life';
            $categoryId = $categoryIds[$categorySlug] ?? reset($categoryIds);

            $schema = $handler->inputSchema();
            $rules = $handler->validationRules();
            $slug = str_replace('_', '-', $key);
            $isFeatured = in_array($key, self::FEATURED_KEYS, true);
            $isPremium = in_array($key, self::PREMIUM_KEYS, true);

            $calculator = Calculator::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'calculator_category_id' => $categoryId,
                    'title' => $meta['title'],
                    'short_description' => $meta['short_description'],
                    'description' => $meta['description'],
                    'icon' => CalculatorIconMap::forCalculator($key),
                    'formula_key' => $key,
                    'formula_description' => $meta['formula_description'],
                    'input_schema' => $schema,
                    'validation_rules' => $rules,
                    'meta_title' => $meta['meta_title'],
                    'meta_description' => $meta['meta_description'],
                    'is_premium' => $isPremium,
                    'is_featured' => $isFeatured,
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );

            $this->calculatorsCreated++;

            $this->seedFaqs($calculator, $meta['faqs']);
            $this->seedExample($calculator, $handler, $meta['title'], $schema);
        }
    }

    /**
     * @param  array<int, array{0: string, 1: string}>  $faqs
     */
    protected function seedFaqs(Calculator $calculator, array $faqs): void
    {
        $calculator->faqs()->delete();

        foreach ($faqs as $index => [$question, $answer]) {
            CalculatorFaq::query()->create([
                'calculator_id' => $calculator->id,
                'question' => $question,
                'answer' => $answer,
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            $this->faqsCreated++;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $schema
     */
    protected function seedExample(Calculator $calculator, object $handler, string $title, array $schema): void
    {
        $calculator->examples()->delete();

        $overrides = self::SAMPLE_OVERRIDES[$calculator->formula_key] ?? [];
        $inputs = $this->buildSampleInputs($schema, $overrides);

        try {
            $result = $handler->calculate($inputs);
            $outputs = $result['results'] ?? [];
        } catch (Throwable $e) {
            Log::warning("CalculatorSeeder: failed to compute example for [{$calculator->formula_key}]: {$e->getMessage()}");
            $outputs = [];
        }

        CalculatorExample::query()->create([
            'calculator_id' => $calculator->id,
            'title' => "Example: {$title}",
            'inputs' => $inputs,
            'outputs' => $outputs,
            'explanation' => 'This example uses typical sample values. Adjust the inputs above to see how the results change for your own numbers.',
            'sort_order' => 1,
        ]);

        $this->examplesCreated++;
    }

    /**
     * Builds a full set of sample inputs for a handler's schema, using
     * (in priority order): an explicit override, the field's own schema
     * default, or a generic type-based fallback for required fields
     * that have neither.
     *
     * @param  array<int, array<string, mixed>>  $schema
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function buildSampleInputs(array $schema, array $overrides): array
    {
        $inputs = [];

        foreach ($schema as $field) {
            $name = $field['name'] ?? null;

            if (! $name) {
                continue;
            }

            if (array_key_exists($name, $overrides)) {
                $inputs[$name] = $overrides[$name];

                continue;
            }

            if (array_key_exists('default', $field)) {
                $inputs[$name] = $field['default'];

                continue;
            }

            $required = $field['required'] ?? true;

            if (! $required) {
                continue;
            }

            $inputs[$name] = match ($field['type'] ?? 'number') {
                'date' => now()->subYears(5)->format('Y-m-d'),
                'time' => '09:00',
                'array' => [],
                'boolean' => false,
                default => 1,
            };
        }

        return $inputs;
    }

    /**
     * Generic fallback metadata for any handler discovered by the
     * registry that isn't present in CALCULATOR_META, so the seeder
     * never fails outright if a new handler is added without content.
     *
     * @return array<string, mixed>
     */
    protected function fallbackMeta(string $key): array
    {
        $title = ucwords(str_replace('_', ' ', $key));
        $categorySlug = $this->categoryMap()[$key] ?? 'daily-life';
        $categoryName = collect(self::CATEGORIES)->firstWhere('slug', $categorySlug)['name'] ?? 'General';

        $built = app(CalculatorContentBuilder::class)->build($key, $title, $categoryName, []);

        return array_merge($built, [
            'icon' => CalculatorIconMap::forCalculator($key),
        ]);
    }

    /**
     * Base CATEGORY_MAP plus generated keys from scripts/generate_missing_calculators.php.
     *
     * @return array<string, string>
     */
    protected function categoryMap(): array
    {
        $generated = [];
        $path = storage_path('app/generated_category_map.php');

        if (is_file($path)) {
            $loaded = require $path;
            if (is_array($loaded)) {
                $generated = $loaded;
            }
        }

        return array_merge(self::CATEGORY_MAP, $generated);
    }
}
