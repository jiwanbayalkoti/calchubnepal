<?php

require_once __DIR__.'/catalog_helpers.php';

$part4 = [];

// ─── Unit converters ──────────────────────────────────────────
$part4[] = converterItem('pressure_converter', 'PressureConverter', 'unit-conversion', 'Pressure Converter', [
    'pa' => 1, 'kpa' => 1000, 'bar' => 100000, 'psi' => 6894.76, 'atm' => 101325, 'mmhg' => 133.322,
], 'bar', 'psi');

$part4[] = converterItem('energy_converter', 'EnergyConverter', 'unit-conversion', 'Energy Converter', [
    'j' => 1, 'kj' => 1000, 'cal' => 4.184, 'kcal' => 4184, 'wh' => 3600, 'kwh' => 3600000, 'btu' => 1055.06,
], 'kwh', 'kj');

$part4[] = converterItem('power_converter', 'PowerConverter', 'unit-conversion', 'Power Converter', [
    'w' => 1, 'kw' => 1000, 'hp' => 745.7, 'btu_h' => 0.293071,
], 'kw', 'hp');

$part4[] = item('fuel_economy_converter', 'FuelEconomyConverter', 'unit-conversion', 'Fuel Economy Converter', schema([
    num('value', 'Value', ['default' => 15, 'min' => 0.01]),
    sel('from_unit', 'From', ['km_l' => 'km/L', 'l_100km' => 'L/100km', 'mpg_us' => 'MPG (US)', 'mpg_uk' => 'MPG (UK)'], 'km_l'),
    sel('to_unit', 'To', ['km_l' => 'km/L', 'l_100km' => 'L/100km', 'mpg_us' => 'MPG (US)', 'mpg_uk' => 'MPG (UK)'], 'mpg_us'),
]), <<<'CODE'
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'km_l');
        $to = $this->toString($inputs, 'to_unit', 'mpg_us');
        $toKmL = match ($from) {
            'l_100km' => $this->safeDivide(100, $value),
            'mpg_us' => $value * 0.425144,
            'mpg_uk' => $value * 0.354006,
            default => $value,
        };
        $converted = match ($to) {
            'l_100km' => $this->safeDivide(100, $toKmL),
            'mpg_us' => $toKmL / 0.425144,
            'mpg_uk' => $toKmL / 0.354006,
            default => $toKmL,
        };
        return [
            'results' => ['converted_value' => $this->round($converted, 4)],
            'breakdown' => ['as_km_per_l' => $this->round($toKmL, 4)],
            'units' => ['converted_value' => $to],
        ];
CODE);

$part4[] = converterItem('data_storage_converter', 'DataStorageConverter', 'unit-conversion', 'Data Storage Converter', [
    'b' => 1, 'kb' => 1024, 'mb' => 1048576, 'gb' => 1073741824, 'tb' => 1099511627776,
], 'gb', 'mb');

$part4[] = converterItem('time_unit_converter', 'TimeUnitConverter', 'unit-conversion', 'Time Unit Converter', [
    'sec' => 1, 'min' => 60, 'hour' => 3600, 'day' => 86400, 'week' => 604800,
], 'hour', 'min');

$part4[] = converterItem('angle_converter', 'AngleConverter', 'unit-conversion', 'Angle Converter', [
    'deg' => 1, 'rad' => 57.2957795, 'grad' => 0.9,
], 'deg', 'rad');

$part4[] = converterItem('density_converter', 'DensityConverter', 'unit-conversion', 'Density Converter', [
    'kg_m3' => 1, 'g_cm3' => 1000, 'lb_ft3' => 16.0185,
], 'kg_m3', 'g_cm3');

// ─── Daily life / date ────────────────────────────────────────
$part4[] = item('working_days_calculator', 'WorkingDaysCalculator', 'daily-life', 'Working Days Calculator', schema([
    "            \$this->field('start_date', 'Start Date', 'date', ['default' => '".date('Y-m-d')."']),",
    "            \$this->field('end_date', 'End Date', 'date', ['default' => '".date('Y-m-d', strtotime('+14 days'))."']),",
]), <<<'CODE'
        $start = \Carbon\Carbon::parse($this->toString($inputs, 'start_date'))->startOfDay();
        $end = \Carbon\Carbon::parse($this->toString($inputs, 'end_date'))->startOfDay();
        if ($end->lt($start)) {
            throw new InvalidArgumentException('End date must be on or after start date.');
        }
        $working = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            if (! $d->isWeekend()) {
                $working++;
            }
        }
        return [
            'results' => [
                'working_days' => $working,
                'calendar_days' => $start->diffInDays($end) + 1,
                'weekend_days' => ($start->diffInDays($end) + 1) - $working,
            ],
            'breakdown' => ['excludes' => 'Saturday & Sunday'],
            'units' => ['working_days' => 'days', 'calendar_days' => 'days', 'weekend_days' => 'days'],
        ];
CODE);

$part4[] = item('business_days_calculator', 'BusinessDaysCalculator', 'daily-life', 'Business Days Calculator', schema([
    "            \$this->field('start_date', 'Start Date', 'date', ['default' => '".date('Y-m-d')."']),",
    num('business_days', 'Business Days To Add', ['default' => 10, 'min' => 1, 'max' => 365, 'step' => 1]),
]), <<<'CODE'
        $date = \Carbon\Carbon::parse($this->toString($inputs, 'start_date'))->startOfDay();
        $need = (int) $this->requireNumeric($inputs, 'business_days');
        $added = 0;
        while ($added < $need) {
            $date->addDay();
            if (! $date->isWeekend()) {
                $added++;
            }
        }
        return [
            'results' => ['result_date' => $date->toDateString()],
            'breakdown' => ['business_days_added' => $need],
            'units' => ['result_date' => 'date'],
        ];
CODE);

$part4[] = item('leap_year_calculator', 'LeapYearCalculator', 'daily-life', 'Leap Year Checker', schema([
    num('year', 'Year', ['default' => (int) date('Y'), 'min' => 1, 'max' => 9999, 'step' => 1]),
]), <<<'CODE'
        $year = (int) $this->requireNumeric($inputs, 'year');
        $isLeap = ($year % 4 === 0 && $year % 100 !== 0) || ($year % 400 === 0);
        return [
            'results' => ['is_leap_year' => $isLeap ? 'Yes' : 'No', 'days_in_year' => $isLeap ? 366 : 365],
            'breakdown' => ['year' => $year],
            'units' => ['is_leap_year' => 'boolean', 'days_in_year' => 'days'],
        ];
CODE);

$part4[] = item('countdown_calculator', 'CountdownCalculator', 'daily-life', 'Countdown Calculator', schema([
    "            \$this->field('target_date', 'Target Date', 'date', ['default' => '".date('Y-m-d', strtotime('+30 days'))."']),",
]), <<<'CODE'
        $target = \Carbon\Carbon::parse($this->toString($inputs, 'target_date'))->startOfDay();
        $now = now()->startOfDay();
        $days = $now->diffInDays($target, false);
        return [
            'results' => [
                'days_remaining' => $days,
                'weeks_remaining' => $this->round($days / 7, 1),
                'status' => $days > 0 ? 'Upcoming' : ($days == 0 ? 'Today' : 'Passed'),
            ],
            'breakdown' => ['target_date' => $target->toDateString()],
            'units' => ['days_remaining' => 'days', 'weeks_remaining' => 'weeks', 'status' => 'text'],
        ];
CODE);

$part4[] = item('time_zone_converter', 'TimeZoneConverter', 'daily-life', 'Time Zone Offset Converter', schema([
    "            \$this->field('time', 'Local Time', 'time', ['default' => '12:00']),",
    num('from_offset', 'From UTC Offset (hours)', ['default' => 5.75, 'min' => -12, 'max' => 14, 'step' => 0.25]),
    num('to_offset', 'To UTC Offset (hours)', ['default' => 0, 'min' => -12, 'max' => 14, 'step' => 0.25]),
]), <<<'CODE'
        $time = $this->toString($inputs, 'time', '12:00');
        [$h, $m] = array_map('intval', explode(':', $time));
        $minutes = $h * 60 + $m;
        $from = $this->requireNumeric($inputs, 'from_offset') * 60;
        $to = $this->requireNumeric($inputs, 'to_offset') * 60;
        $utc = $minutes - $from;
        $target = $utc + $to;
        while ($target < 0) { $target += 1440; }
        while ($target >= 1440) { $target -= 1440; }
        $out = sprintf('%02d:%02d', intdiv((int) $target, 60), ((int) $target) % 60);
        return [
            'results' => ['converted_time' => $out],
            'breakdown' => ['note' => 'Offset-based conversion (no DST rules)'],
            'units' => ['converted_time' => 'HH:MM'],
        ];
CODE);

$part4[] = item('tip_calculator', 'TipCalculator', 'daily-life', 'Tip Calculator', schema([
    num('bill', 'Bill Amount', ['default' => 1500, 'unit' => 'currency']),
    num('tip_percent', 'Tip %', ['default' => 10, 'unit' => '%', 'max' => 50]),
    num('people', 'Split Between', ['default' => 1, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $bill = $this->requireNumeric($inputs, 'bill');
        $tip = $bill * $this->requireNumeric($inputs, 'tip_percent') / 100;
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $total = $bill + $tip;
        return [
            'results' => [
                'tip_amount' => $this->round($tip),
                'total_with_tip' => $this->round($total),
                'per_person' => $this->round($total / $people),
            ],
            'breakdown' => ['people' => $people],
            'units' => ['tip_amount' => 'currency', 'total_with_tip' => 'currency', 'per_person' => 'currency'],
        ];
CODE);

$part4[] = item('split_bill_calculator', 'SplitBillCalculator', 'daily-life', 'Split Bill Calculator', schema([
    num('total', 'Total Amount', ['default' => 3000, 'unit' => 'currency']),
    num('people', 'Number of People', ['default' => 4, 'min' => 1, 'step' => 1]),
    num('tip_percent', 'Tip % (optional)', ['default' => 0, 'unit' => '%', 'max' => 50, 'required' => false]),
]), <<<'CODE'
        $total = $this->requireNumeric($inputs, 'total');
        $tip = $total * $this->toFloat($inputs, 'tip_percent') / 100;
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $grand = $total + $tip;
        return [
            'results' => [
                'grand_total' => $this->round($grand),
                'per_person' => $this->round($grand / $people),
            ],
            'breakdown' => ['tip_amount' => $this->round($tip)],
            'units' => ['grand_total' => 'currency', 'per_person' => 'currency'],
        ];
CODE);

$part4[] = item('cooking_converter', 'CookingConverter', 'daily-life', 'Cooking Converter', schema([
    num('value', 'Amount', ['default' => 1, 'min' => 0]),
    sel('from_unit', 'From', [
        'tsp' => 'Teaspoon', 'tbsp' => 'Tablespoon', 'cup' => 'Cup', 'ml' => 'Milliliter', 'g' => 'Gram (water)',
    ], 'cup'),
    sel('to_unit', 'To', [
        'tsp' => 'Teaspoon', 'tbsp' => 'Tablespoon', 'cup' => 'Cup', 'ml' => 'Milliliter', 'g' => 'Gram (water)',
    ], 'ml'),
]), <<<'CODE'
        $toMl = ['tsp' => 4.92892, 'tbsp' => 14.7868, 'cup' => 240, 'ml' => 1, 'g' => 1];
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'cup');
        $to = $this->toString($inputs, 'to_unit', 'ml');
        $ml = $value * $toMl[$from];
        $converted = $ml / $toMl[$to];
        return [
            'results' => ['converted_value' => $this->round($converted, 4)],
            'breakdown' => ['ml_equivalent' => $this->round($ml, 4)],
            'units' => ['converted_value' => $to],
        ];
CODE);

$part4[] = item('recipe_scaler_calculator', 'RecipeScalerCalculator', 'daily-life', 'Recipe Scaler', schema([
    num('original_servings', 'Original Servings', ['default' => 4, 'min' => 1]),
    num('desired_servings', 'Desired Servings', ['default' => 6, 'min' => 1]),
    num('ingredient_amount', 'Ingredient Amount', ['default' => 2]),
]), <<<'CODE'
        $factor = $this->safeDivide($this->requireNumeric($inputs, 'desired_servings'), $this->requireNumeric($inputs, 'original_servings'));
        $scaled = $this->requireNumeric($inputs, 'ingredient_amount') * $factor;
        return [
            'results' => ['scale_factor' => $this->round($factor, 4), 'scaled_amount' => $this->round($scaled, 3)],
            'breakdown' => ['formula' => 'desired / original'],
            'units' => ['scale_factor' => '×', 'scaled_amount' => 'amount'],
        ];
CODE);

$part4[] = item('sleep_calculator', 'SleepCalculator', 'daily-life', 'Sleep Cycle Calculator', schema([
    "            \$this->field('wake_time', 'Wake-up Time', 'time', ['default' => '06:30']),",
    num('cycles', 'Sleep Cycles (90 min)', ['default' => 5, 'min' => 1, 'max' => 8, 'step' => 1]),
]), <<<'CODE'
        [$h, $m] = array_map('intval', explode(':', $this->toString($inputs, 'wake_time', '06:30')));
        $wake = $h * 60 + $m;
        $cycles = (int) $this->requireNumeric($inputs, 'cycles');
        $bed = $wake - ($cycles * 90) - 15; // 15 min fall-asleep buffer
        while ($bed < 0) { $bed += 1440; }
        $bedTime = sprintf('%02d:%02d', intdiv($bed, 60), $bed % 60);
        return [
            'results' => [
                'suggested_bedtime' => $bedTime,
                'sleep_duration_hours' => $this->round(($cycles * 90) / 60, 1),
            ],
            'breakdown' => ['cycles' => $cycles, 'cycle_minutes' => 90],
            'units' => ['suggested_bedtime' => 'HH:MM', 'sleep_duration_hours' => 'hours'],
        ];
CODE);

// ─── Home ─────────────────────────────────────────────────────
$part4[] = item('electricity_bill_calculator', 'ElectricityBillCalculator', 'home', 'Electricity Bill Calculator', schema([
    num('units', 'Units Consumed (kWh)', ['default' => 200, 'min' => 0]),
    num('rate_per_unit', 'Rate Per Unit', ['default' => 12, 'unit' => 'currency']),
    num('fixed_charge', 'Fixed Charge', ['default' => 100, 'unit' => 'currency', 'min' => 0]),
]), <<<'CODE'
        $units = $this->requireNumeric($inputs, 'units');
        $rate = $this->requireNumeric($inputs, 'rate_per_unit');
        $fixed = $this->requireNumeric($inputs, 'fixed_charge');
        $energy = $units * $rate;
        return [
            'results' => [
                'energy_charge' => $this->round($energy),
                'total_bill' => $this->round($energy + $fixed),
            ],
            'breakdown' => ['units' => $units],
            'units' => ['energy_charge' => 'currency', 'total_bill' => 'currency'],
        ];
CODE);

$part4[] = item('water_bill_calculator', 'WaterBillCalculator', 'home', 'Water Bill Calculator', schema([
    num('consumption', 'Consumption', ['default' => 15, 'unit' => 'm³', 'min' => 0]),
    num('rate', 'Rate Per m³', ['default' => 25, 'unit' => 'currency']),
    num('fixed_charge', 'Fixed Charge', ['default' => 50, 'unit' => 'currency', 'min' => 0]),
]), <<<'CODE'
        $usage = $this->requireNumeric($inputs, 'consumption') * $this->requireNumeric($inputs, 'rate');
        $fixed = $this->requireNumeric($inputs, 'fixed_charge');
        return [
            'results' => ['usage_charge' => $this->round($usage), 'total_bill' => $this->round($usage + $fixed)],
            'breakdown' => ['consumption_m3' => $this->requireNumeric($inputs, 'consumption')],
            'units' => ['usage_charge' => 'currency', 'total_bill' => 'currency'],
        ];
CODE);

$part4[] = item('ac_size_calculator', 'AcSizeCalculator', 'home', 'AC Size Calculator', schema([
    num('room_area', 'Room Area', ['default' => 150, 'unit' => 'sq.ft', 'min' => 50]),
    num('occupants', 'Occupants', ['default' => 2, 'min' => 1, 'step' => 1]),
    sel('sunlight', 'Sunlight', ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'], 'medium'),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'room_area');
        $people = $this->requireNumeric($inputs, 'occupants');
        $factor = match ($this->toString($inputs, 'sunlight', 'medium')) {
            'low' => 0.9, 'high' => 1.2, default => 1.0,
        };
        $btu = ($area * 25 + $people * 600) * $factor;
        $tons = $btu / 12000;
        return [
            'results' => [
                'estimated_btu' => $this->round($btu),
                'recommended_tons' => $this->round($tons, 2),
            ],
            'breakdown' => ['note' => 'Rule-of-thumb estimate — verify with HVAC professional'],
            'units' => ['estimated_btu' => 'BTU/h', 'recommended_tons' => 'ton'],
        ];
CODE);

$part4[] = item('solar_requirement_calculator', 'SolarRequirementCalculator', 'home', 'Home Solar Requirement', schema([
    num('monthly_kwh', 'Monthly Usage', ['default' => 300, 'unit' => 'kWh']),
    num('sun_hours', 'Peak Sun Hours', ['default' => 4.5, 'min' => 1, 'max' => 8]),
]), <<<'CODE'
        $daily = $this->requireNumeric($inputs, 'monthly_kwh') / 30;
        $kw = $this->safeDivide($daily, $this->requireNumeric($inputs, 'sun_hours') * 0.8);
        return [
            'results' => [
                'daily_kwh' => $this->round($daily, 2),
                'recommended_system_kw' => $this->round($kw, 2),
            ],
            'breakdown' => ['efficiency_assumed' => '80%'],
            'units' => ['daily_kwh' => 'kWh/day', 'recommended_system_kw' => 'kW'],
        ];
CODE);

$part4[] = item('room_area_calculator', 'RoomAreaCalculator', 'home', 'Room Area Calculator', schema([
    num('length', 'Length', ['default' => 12, 'unit' => 'ft']),
    num('width', 'Width', ['default' => 10, 'unit' => 'ft']),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        return [
            'results' => [
                'area_sqft' => $this->round($area, 2),
                'area_m2' => $this->round($area * 0.092903, 2),
            ],
            'breakdown' => ['shape' => 'rectangle'],
            'units' => ['area_sqft' => 'sq.ft', 'area_m2' => 'm²'],
        ];
CODE);

$part4[] = item('carpet_area_calculator', 'CarpetAreaCalculator', 'home', 'Carpet Area Calculator', schema([
    num('built_up_area', 'Built-up Area', ['default' => 1200, 'unit' => 'sq.ft']),
    num('carpet_ratio', 'Carpet Area Ratio', ['default' => 70, 'unit' => '%', 'min' => 50, 'max' => 90]),
]), <<<'CODE'
        $built = $this->requireNumeric($inputs, 'built_up_area');
        $ratio = $this->requireNumeric($inputs, 'carpet_ratio') / 100;
        $carpet = $built * $ratio;
        return [
            'results' => [
                'carpet_area' => $this->round($carpet, 2),
                'non_carpet_area' => $this->round($built - $carpet, 2),
            ],
            'breakdown' => ['built_up_area' => $built],
            'units' => ['carpet_area' => 'sq.ft', 'non_carpet_area' => 'sq.ft'],
        ];
CODE);

$part4[] = item('curtain_length_calculator', 'CurtainLengthCalculator', 'home', 'Curtain Length Calculator', schema([
    num('window_height', 'Window Height', ['default' => 60, 'unit' => 'in']),
    num('extra_drop', 'Extra Drop / Hem', ['default' => 8, 'unit' => 'in', 'min' => 0]),
    num('window_width', 'Window Width', ['default' => 48, 'unit' => 'in']),
    num('fullness', 'Fullness Factor', ['default' => 2, 'min' => 1, 'max' => 3, 'step' => 0.1]),
]), <<<'CODE'
        $length = $this->requireNumeric($inputs, 'window_height') + $this->requireNumeric($inputs, 'extra_drop');
        $width = $this->requireNumeric($inputs, 'window_width') * $this->requireNumeric($inputs, 'fullness');
        return [
            'results' => [
                'curtain_length_in' => $this->round($length, 1),
                'fabric_width_in' => $this->round($width, 1),
            ],
            'breakdown' => ['fullness' => $this->requireNumeric($inputs, 'fullness')],
            'units' => ['curtain_length_in' => 'in', 'fabric_width_in' => 'in'],
        ];
CODE);

// ─── Automobile ───────────────────────────────────────────────
$part4[] = item('fuel_cost_calculator', 'FuelCostCalculator', 'automobile', 'Fuel Cost Calculator', schema([
    num('distance', 'Distance', ['default' => 200, 'unit' => 'km']),
    num('mileage', 'Mileage', ['default' => 15, 'unit' => 'km/L', 'min' => 1]),
    num('fuel_price', 'Fuel Price / L', ['default' => 180, 'unit' => 'currency']),
]), <<<'CODE'
        $liters = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'mileage'));
        $cost = $liters * $this->requireNumeric($inputs, 'fuel_price');
        return [
            'results' => ['fuel_needed_liters' => $this->round($liters, 2), 'trip_fuel_cost' => $this->round($cost)],
            'breakdown' => ['distance_km' => $this->requireNumeric($inputs, 'distance')],
            'units' => ['fuel_needed_liters' => 'L', 'trip_fuel_cost' => 'currency'],
        ];
CODE);

$part4[] = item('mileage_calculator', 'MileageCalculator', 'automobile', 'Mileage Calculator', schema([
    num('distance', 'Distance Travelled', ['default' => 300, 'unit' => 'km']),
    num('fuel_used', 'Fuel Used', ['default' => 20, 'unit' => 'L', 'min' => 0.1]),
]), <<<'CODE'
        $mileage = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'fuel_used'));
        return [
            'results' => ['mileage_km_l' => $this->round($mileage, 2), 'l_per_100km' => $this->round(100 / $mileage, 2)],
            'breakdown' => [],
            'units' => ['mileage_km_l' => 'km/L', 'l_per_100km' => 'L/100km'],
        ];
CODE);

$part4[] = item('tire_size_calculator', 'TireSizeCalculator', 'automobile', 'Tire Size Calculator', schema([
    num('width', 'Width', ['default' => 205, 'unit' => 'mm']),
    num('aspect', 'Aspect Ratio', ['default' => 55, 'unit' => '%']),
    num('rim', 'Rim Diameter', ['default' => 16, 'unit' => 'in']),
]), <<<'CODE'
        $width = $this->requireNumeric($inputs, 'width');
        $aspect = $this->requireNumeric($inputs, 'aspect');
        $rim = $this->requireNumeric($inputs, 'rim');
        $sidewall = $width * ($aspect / 100);
        $diameterMm = ($rim * 25.4) + (2 * $sidewall);
        $circumference = pi() * $diameterMm;
        return [
            'results' => [
                'sidewall_mm' => $this->round($sidewall, 1),
                'overall_diameter_mm' => $this->round($diameterMm, 1),
                'circumference_m' => $this->round($circumference / 1000, 3),
            ],
            'breakdown' => ['size_code' => "{$width}/{$aspect}R{$rim}"],
            'units' => ['sidewall_mm' => 'mm', 'overall_diameter_mm' => 'mm', 'circumference_m' => 'm'],
        ];
CODE);

$part4[] = item('vehicle_speed_calculator', 'VehicleSpeedCalculator', 'automobile', 'Speed / Time / Distance', schema([
    sel('solve_for', 'Solve For', ['speed' => 'Speed', 'time' => 'Time', 'distance' => 'Distance'], 'speed'),
    num('distance', 'Distance (km)', ['default' => 120, 'required' => false]),
    num('time_hours', 'Time (hours)', ['default' => 2, 'required' => false, 'min' => 0.01]),
    num('speed', 'Speed (km/h)', ['default' => 60, 'required' => false, 'min' => 0.01]),
]), <<<'CODE'
        $mode = $this->toString($inputs, 'solve_for', 'speed');
        $d = $this->toFloat($inputs, 'distance');
        $t = $this->toFloat($inputs, 'time_hours');
        $s = $this->toFloat($inputs, 'speed');
        $value = match ($mode) {
            'time' => $this->safeDivide($d, $s),
            'distance' => $s * $t,
            default => $this->safeDivide($d, $t),
        };
        $label = match ($mode) { 'time' => 'time_hours', 'distance' => 'distance_km', default => 'speed_kmh' };
        return [
            'results' => [$label => $this->round($value, 3)],
            'breakdown' => ['formula' => 'distance = speed × time'],
            'units' => [$label => match ($mode) { 'time' => 'hours', 'distance' => 'km', default => 'km/h' }],
        ];
CODE);

$part4[] = item('road_trip_cost_calculator', 'RoadTripCostCalculator', 'automobile', 'Road Trip Cost Calculator', schema([
    num('distance', 'Round-trip Distance', ['default' => 500, 'unit' => 'km']),
    num('mileage', 'Mileage', ['default' => 14, 'unit' => 'km/L']),
    num('fuel_price', 'Fuel Price / L', ['default' => 180, 'unit' => 'currency']),
    num('other_costs', 'Toll / Food / Misc', ['default' => 2000, 'unit' => 'currency', 'min' => 0]),
    num('people', 'People Sharing', ['default' => 2, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $fuel = $this->safeDivide($this->requireNumeric($inputs, 'distance'), $this->requireNumeric($inputs, 'mileage')) * $this->requireNumeric($inputs, 'fuel_price');
        $other = $this->requireNumeric($inputs, 'other_costs');
        $people = max(1, $this->requireNumeric($inputs, 'people'));
        $total = $fuel + $other;
        return [
            'results' => [
                'fuel_cost' => $this->round($fuel),
                'total_cost' => $this->round($total),
                'cost_per_person' => $this->round($total / $people),
            ],
            'breakdown' => ['people' => $people],
            'units' => ['fuel_cost' => 'currency', 'total_cost' => 'currency', 'cost_per_person' => 'currency'],
        ];
CODE);

$part4[] = item('ev_charging_calculator', 'EvChargingCalculator', 'automobile', 'EV Charging Calculator', schema([
    num('battery_kwh', 'Battery Capacity', ['default' => 40, 'unit' => 'kWh']),
    num('charge_from', 'Charge From %', ['default' => 20, 'unit' => '%', 'max' => 100]),
    num('charge_to', 'Charge To %', ['default' => 80, 'unit' => '%', 'max' => 100]),
    num('rate_per_kwh', 'Electricity Rate', ['default' => 12, 'unit' => 'currency']),
    num('charger_kw', 'Charger Power', ['default' => 7, 'unit' => 'kW', 'min' => 1]),
]), <<<'CODE'
        $from = $this->requireNumeric($inputs, 'charge_from');
        $to = $this->requireNumeric($inputs, 'charge_to');
        if ($to <= $from) {
            throw new InvalidArgumentException('Charge-to % must be greater than charge-from %.');
        }
        $kwh = $this->requireNumeric($inputs, 'battery_kwh') * (($to - $from) / 100);
        $cost = $kwh * $this->requireNumeric($inputs, 'rate_per_kwh');
        $hours = $this->safeDivide($kwh, $this->requireNumeric($inputs, 'charger_kw'));
        return [
            'results' => [
                'energy_added_kwh' => $this->round($kwh, 2),
                'charging_cost' => $this->round($cost),
                'approx_time_hours' => $this->round($hours, 2),
            ],
            'breakdown' => ['soc_window' => "{$from}% → {$to}%"],
            'units' => ['energy_added_kwh' => 'kWh', 'charging_cost' => 'currency', 'approx_time_hours' => 'hours'],
        ];
CODE);

$part4[] = item('battery_life_calculator', 'BatteryLifeCalculator', 'automobile', 'Device Battery Life Calculator', schema([
    num('capacity_mah', 'Battery Capacity', ['default' => 5000, 'unit' => 'mAh']),
    num('load_ma', 'Average Load', ['default' => 500, 'unit' => 'mA', 'min' => 1]),
    num('efficiency', 'Efficiency', ['default' => 90, 'unit' => '%', 'min' => 50, 'max' => 100]),
]), <<<'CODE'
        $hours = $this->safeDivide(
            $this->requireNumeric($inputs, 'capacity_mah') * ($this->requireNumeric($inputs, 'efficiency') / 100),
            $this->requireNumeric($inputs, 'load_ma')
        );
        return [
            'results' => ['estimated_hours' => $this->round($hours, 2), 'estimated_days' => $this->round($hours / 24, 2)],
            'breakdown' => ['formula' => 'hours = (mAh × efficiency) / mA'],
            'units' => ['estimated_hours' => 'hours', 'estimated_days' => 'days'],
        ];
CODE);

return $part4;
