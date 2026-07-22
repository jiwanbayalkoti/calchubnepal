<?php

namespace App\Support;

/**
 * Bootstrap Icons for categories and calculators.
 * Views render: <i class="bi {{ $icon }}"></i>
 */
class CalculatorIconMap
{
    /**
     * @return array<string, string> category slug => bi-* icon
     */
    public static function categories(): array
    {
        return [
            'construction' => 'bi-bricks',
            'finance' => 'bi-cash-coin',
            'health' => 'bi-heart-pulse',
            'education' => 'bi-mortarboard',
            'business' => 'bi-briefcase',
            'unit-conversion' => 'bi-arrow-left-right',
            'daily-life' => 'bi-calendar-day',
            'engineering' => 'bi-gear-wide-connected',
            'basic-math' => 'bi-calculator',
            'fitness' => 'bi-trophy',
            'home' => 'bi-house-heart',
            'automobile' => 'bi-car-front',
            'agriculture' => 'bi-flower1',
            'real-estate' => 'bi-buildings',
            'internet-it' => 'bi-laptop',
            'developer' => 'bi-code-slash',
            'nepal' => 'bi-flag',
            'career' => 'bi-person-badge',
            'climate-energy' => 'bi-sun',
            'tax-deductions' => 'bi-receipt',
            'productivity' => 'bi-lightning-charge',
        ];
    }

    /**
     * @return array<string, string> formula_key => bi-* icon
     */
    public static function calculators(): array
    {
        return [
            // Construction
            'brick_calculator' => 'bi-bricks',
            'cement_calculator' => 'bi-box-seam',
            'concrete_calculator' => 'bi-square-fill',
            'steel_calculator' => 'bi-grip-horizontal',
            'tile_calculator' => 'bi-grid-3x3',
            'paint_calculator' => 'bi-paint-bucket',
            'sand_calculator' => 'bi-moisture',
            'aggregate_calculator' => 'bi-circle-square',
            'plaster_calculator' => 'bi-layers',
            'excavation_calculator' => 'bi-truck',
            'boq_calculator' => 'bi-clipboard-data',
            'flooring_calculator' => 'bi-grid',
            'house_cost_calculator' => 'bi-house-door',
            'marble_calculator' => 'bi-diamond',
            'pcc_calculator' => 'bi-bounding-box',
            'rcc_calculator' => 'bi-building',
            'rebar_calculator' => 'bi-distribute-vertical',
            'roofing_calculator' => 'bi-house',
            'septic_tank_calculator' => 'bi-droplet-half',
            'stair_calculator' => 'bi-diagram-2',
            'wallpaper_calculator' => 'bi-card-image',
            'water_tank_calculator' => 'bi-droplet',

            // Finance
            'emi_calculator' => 'bi-credit-card',
            'loan_calculator' => 'bi-bank',
            'mortgage_calculator' => 'bi-house-door',
            'profit_calculator' => 'bi-graph-up-arrow',
            'margin_calculator' => 'bi-percent',
            'gst_calculator' => 'bi-receipt',
            'vat_calculator' => 'bi-receipt-cutoff',
            'salary_tax_calculator' => 'bi-file-earmark-text',
            'roi_calculator' => 'bi-pie-chart',
            'sip_calculator' => 'bi-piggy-bank',
            'compound_interest_calculator' => 'bi-coin',
            'simple_interest_calculator' => 'bi-cash',
            'fd_calculator' => 'bi-safe',
            'rd_calculator' => 'bi-wallet2',
            'inflation_calculator' => 'bi-graph-up',
            'mutual_fund_calculator' => 'bi-bar-chart-steps',
            'pension_calculator' => 'bi-person-badge',
            'retirement_calculator' => 'bi-hourglass-split',
            'break_even_calculator' => 'bi-signpost-split',

            // Health
            'bmi_calculator' => 'bi-speedometer2',
            'bmr_calculator' => 'bi-fire',
            'body_fat_calculator' => 'bi-person',
            'calorie_calculator' => 'bi-apple',
            'water_intake_calculator' => 'bi-cup-straw',
            'body_surface_area_calculator' => 'bi-person-bounding-box',
            'ideal_weight_calculator' => 'bi-clipboard2-pulse',
            'macro_calculator' => 'bi-egg-fried',
            'ovulation_calculator' => 'bi-calendar-heart',
            'pregnancy_due_date_calculator' => 'bi-hearts',
            'pregnancy_week_calculator' => 'bi-calendar-week',
            'protein_intake_calculator' => 'bi-basket',
            'sleep_calculator' => 'bi-moon-stars',

            // Education
            'gpa_calculator' => 'bi-mortarboard',
            'percentage_calculator' => 'bi-percent',
            'cgpa_calculator' => 'bi-award',
            'grade_calculator' => 'bi-journal-check',
            'marks_calculator' => 'bi-pencil-square',
            'study_time_calculator' => 'bi-book',
            'attendance_calculator' => 'bi-calendar-check',

            // Business
            'discount_calculator' => 'bi-tags',
            'commission_calculator' => 'bi-handshake',
            'payroll_calculator' => 'bi-cash-stack',
            'ebitda_calculator' => 'bi-clipboard2-data',
            'inventory_calculator' => 'bi-boxes',
            'invoice_calculator' => 'bi-file-earmark-ruled',
            'roe_calculator' => 'bi-graph-up-arrow',
            'salary_calculator' => 'bi-wallet',
            'sales_tax_calculator' => 'bi-receipt',

            // Unit conversion
            'length_converter' => 'bi-rulers',
            'area_converter' => 'bi-aspect-ratio',
            'volume_converter' => 'bi-beaker',
            'weight_converter' => 'bi-speedometer',
            'temperature_converter' => 'bi-thermometer-half',
            'speed_converter' => 'bi-speedometer2',
            'angle_converter' => 'bi-triangle',
            'cooking_converter' => 'bi-cup-hot',
            'data_storage_converter' => 'bi-hdd',
            'density_converter' => 'bi-droplet',
            'energy_converter' => 'bi-lightning',
            'fuel_economy_converter' => 'bi-fuel-pump',
            'power_converter' => 'bi-plug',
            'pressure_converter' => 'bi-activity',
            'time_unit_converter' => 'bi-stopwatch',

            // Daily life
            'age_calculator' => 'bi-cake2',
            'date_difference_calculator' => 'bi-calendar-range',
            'time_calculator' => 'bi-clock-history',
            'business_days_calculator' => 'bi-calendar2-week',
            'countdown_calculator' => 'bi-hourglass',
            'leap_year_calculator' => 'bi-calendar3',
            'recipe_scaler_calculator' => 'bi-journal-richtext',
            'split_bill_calculator' => 'bi-people',
            'tip_calculator' => 'bi-cash-coin',
            'time_zone_converter' => 'bi-globe2',
            'working_days_calculator' => 'bi-briefcase',

            // Engineering
            'beam_calculator' => 'bi-distribute-horizontal',
            'slab_calculator' => 'bi-layers-half',
            'column_calculator' => 'bi-view-stacked',
            'footing_calculator' => 'bi-square',
            'ohms_law_calculator' => 'bi-lightning-charge',
            'pipe_flow_calculator' => 'bi-water',
            'pipe_volume_calculator' => 'bi-droplet-half',
            'solar_panel_calculator' => 'bi-sun',
            'battery_backup_calculator' => 'bi-battery-charging',
            'ups_calculator' => 'bi-battery-full',
            'cable_size_calculator' => 'bi-ethernet',
            'horsepower_calculator' => 'bi-speedometer',
            'kva_calculator' => 'bi-lightning',
            'pressure_drop_calculator' => 'bi-arrow-down-up',
            'rpm_calculator' => 'bi-arrow-repeat',
            'torque_calculator' => 'bi-gear',
            'transformer_calculator' => 'bi-magnet',
            'watt_calculator' => 'bi-lightning-fill',

            // Basic math
            'average_calculator' => 'bi-bar-chart',
            'decimal_calculator' => 'bi-123',
            'exponent_calculator' => 'bi-superscript',
            'factorial_calculator' => 'bi-exclamation-lg',
            'fraction_calculator' => 'bi-slash-lg',
            'gcd_calculator' => 'bi-diagram-3',
            'lcm_calculator' => 'bi-intersect',
            'log_calculator' => 'bi-graph-down',
            'median_calculator' => 'bi-bar-chart-line',
            'mode_calculator' => 'bi-bar-chart-steps',
            'prime_number_calculator' => 'bi-hash',
            'probability_calculator' => 'bi-dice-5',
            'proportion_calculator' => 'bi-aspect-ratio-fill',
            'quadratic_calculator' => 'bi-graph-up',
            'random_number_generator' => 'bi-shuffle',
            'ratio_calculator' => 'bi-sliders',
            'standard_deviation_calculator' => 'bi-bar-chart-line-fill',
            'variance_calculator' => 'bi-graph-up-arrow',

            // Fitness
            'tdee_calculator' => 'bi-fire',
            'calories_burned_calculator' => 'bi-fire',
            'cycling_speed_calculator' => 'bi-bicycle',
            'heart_rate_zone_calculator' => 'bi-heart-pulse',
            'lean_mass_calculator' => 'bi-person',
            'one_rep_max_calculator' => 'bi-trophy',
            'running_pace_calculator' => 'bi-stopwatch',
            'target_heart_rate_calculator' => 'bi-heart',
            'vo2_max_calculator' => 'bi-heart-pulse',

            // Home
            'carpet_area_calculator' => 'bi-bounding-box-circles',
            'curtain_length_calculator' => 'bi-window',
            'electricity_bill_calculator' => 'bi-plug',
            'room_area_calculator' => 'bi-house',
            'solar_requirement_calculator' => 'bi-sun-fill',
            'water_bill_calculator' => 'bi-droplet-fill',

            // Climate & Energy
            'solar_roi_calculator' => 'bi-sun',
            'backup_power_roi_calculator' => 'bi-battery-charging',
            'solar_panel_cost_calculator' => 'bi-solar-panel',
            'ev_vs_ice_tco_calculator' => 'bi-ev-front',
            'ac_size_calculator' => 'bi-snow',
            'heat_pump_payback_calculator' => 'bi-thermometer-half',
            'home_insulation_roi_calculator' => 'bi-house-gear',
            'whole_home_electrification_bundle_roi_calculator' => 'bi-lightning',
            'flood_insurance_vs_self_insure_calculator' => 'bi-water',
            'home_climate_hardening_payback_calculator' => 'bi-shield-check',
            'wildfire_defensible_space_roi_calculator' => 'bi-tree',
            'carbon_footprint_true_cost_calculator' => 'bi-globe2',
            'flight_emissions_offset_calculator' => 'bi-airplane',
            'electricity_bill_optimizer_tou_calculator' => 'bi-graph-up-arrow',
            'climate_migration_cost_calculator' => 'bi-geo-alt',

            // Tax & Deductions
            'kids_529_vs_utma_vs_roth_calculator' => 'bi-mortarboard',
            'multi_state_remote_work_tax_exposure_calculator' => 'bi-geo',
            'hsa_triple_tax_optimizer_calculator' => 'bi-heart-pulse',
            'bonus_tax_calculator' => 'bi-cash-stack',
            'mega_backdoor_roth_calculator' => 'bi-door-open',
            'backdoor_roth_pro_rata_trap_calculator' => 'bi-exclamation-triangle',
            'property_tax_calculator' => 'bi-house',
            'quarterly_estimated_tax_calculator' => 'bi-calendar4',
            'dependent_care_fsa_vs_child_tax_credit_calculator' => 'bi-people',
            'capital_gains_tax_calculator' => 'bi-graph-up',
            'after_tax_income_calculator' => 'bi-wallet2',
            'rsu_tax_withholding_shortfall_calculator' => 'bi-pie-chart',
            'stock_options_iso_nso_amt_calculator' => 'bi-bar-chart-steps',
            'tax_bracket_calculator' => 'bi-layers',
            'digital_nomad_tax_residency_optimizer_calculator' => 'bi-globe',

            // Productivity
            'compound_habit_calculator' => 'bi-graph-up-arrow',
            'probability_of_success_calculator' => 'bi-dice-5',
            'cognitive_load_calculator' => 'bi-cpu',
            'best_day_to_move_calculator' => 'bi-calendar-check',
            'decision_fatigue_calculator' => 'bi-battery-half',

            // Automobile
            'auto_refinance_calculator' => 'bi-arrow-repeat',
            'battery_life_calculator' => 'bi-battery-half',
            'commute_cost_time_calculator' => 'bi-briefcase',
            'ev_charging_calculator' => 'bi-ev-station',
            'ev_vs_gas_total_cost_calculator' => 'bi-lightning-charge',
            'fuel_cost_calculator' => 'bi-fuel-pump',
            'lease_vs_buy_car_calculator' => 'bi-shuffle',
            'mileage_calculator' => 'bi-speedometer2',
            'road_trip_cost_calculator' => 'bi-signpost-2',
            'tire_size_calculator' => 'bi-circle',
            'true_cost_per_mile_calculator' => 'bi-cash-coin',
            'vehicle_speed_calculator' => 'bi-speedometer',

            // Agriculture
            'crop_yield_calculator' => 'bi-basket2',
            'fertilizer_calculator' => 'bi-moisture',
            'irrigation_calculator' => 'bi-droplet',
            'livestock_feed_calculator' => 'bi-egg',
            'seed_calculator' => 'bi-flower2',

            // Real estate
            'rent_calculator' => 'bi-key',
            'rental_yield_calculator' => 'bi-graph-up',

            // Internet / IT / Developer
            'password_strength_calculator' => 'bi-shield-lock',
            'api_cost_calculator' => 'bi-cloud',
            'openai_token_calculator' => 'bi-robot',

            // Nepal
            'aana_sqm_converter' => 'bi-map',
            'dashain_allowance_calculator' => 'bi-gift',
            'dhur_converter' => 'bi-geo',
            'driving_license_fee_calculator' => 'bi-card-heading',
            'gratuity_calculator' => 'bi-cash-coin',
            'ipo_allotment_calculator' => 'bi-graph-up',
            'land_measurement_nepal_calculator' => 'bi-map-fill',
            'nepal_house_cost_calculator' => 'bi-house',
            'nepal_income_tax_calculator' => 'bi-file-earmark-bar-graph',
            'nepal_tds_calculator' => 'bi-receipt',
            'nepal_vat_calculator' => 'bi-receipt-cutoff',
            'nepse_brokerage_calculator' => 'bi-bar-chart',
            'passport_fee_calculator' => 'bi-card-text',
            'provident_fund_calculator' => 'bi-safe',
            'ropani_sqft_converter' => 'bi-pin-map',
            'tds_calculator' => 'bi-file-earmark-diff',
        ];
    }

    public static function forCalculator(string $formulaKey): string
    {
        $map = self::calculators();

        return $map[$formulaKey] ?? 'bi-calculator';
    }

    public static function forCategory(string $slug): string
    {
        $map = self::categories();

        return $map[$slug] ?? 'bi-grid';
    }
}
