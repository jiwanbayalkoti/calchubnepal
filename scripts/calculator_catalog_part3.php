<?php

require_once __DIR__.'/catalog_helpers.php';

$part3 = [];

// ─── Health ───────────────────────────────────────────────────
$part3[] = item('ideal_weight_calculator', 'IdealWeightCalculator', 'health', 'Ideal Weight Calculator', schema([
    num('height_cm', 'Height', ['default' => 170, 'unit' => 'cm', 'min' => 100, 'max' => 250]),
    sel('gender', 'Gender', ['male' => 'Male', 'female' => 'Female'], 'male'),
]), <<<'CODE'
        $h = $this->requireNumeric($inputs, 'height_cm');
        $gender = $this->toString($inputs, 'gender', 'male');
        // Devine formula
        $inches = $h / 2.54;
        $base = $gender === 'female' ? 45.5 : 50;
        $ideal = $base + 2.3 * max(0, $inches - 60);
        return [
            'results' => ['ideal_weight_kg' => $this->round($ideal, 1), 'ideal_weight_lb' => $this->round($ideal * 2.20462, 1)],
            'breakdown' => ['formula' => 'Devine formula', 'gender' => $gender],
            'units' => ['ideal_weight_kg' => 'kg', 'ideal_weight_lb' => 'lb'],
        ];
CODE);

$part3[] = item('pregnancy_due_date_calculator', 'PregnancyDueDateCalculator', 'health', 'Pregnancy Due Date Calculator', schema([
    "            \$this->field('lmp_date', 'Last Menstrual Period (LMP)', 'date', ['default' => '".date('Y-m-d', strtotime('-4 weeks'))."']),",
]), <<<'CODE'
        $lmp = $this->toString($inputs, 'lmp_date');
        $start = \Carbon\Carbon::parse($lmp);
        $due = $start->copy()->addDays(280);
        $today = now()->startOfDay();
        $day = $start->diffInDays($today);
        $week = intdiv(max(0, $day), 7);
        return [
            'results' => [
                'due_date' => $due->toDateString(),
                'current_week' => min(40, $week),
                'days_remaining' => max(0, $today->diffInDays($due, false)),
            ],
            'breakdown' => ['method' => 'Naegele (LMP + 280 days)'],
            'units' => ['due_date' => 'date', 'current_week' => 'weeks', 'days_remaining' => 'days'],
        ];
CODE);

$part3[] = item('ovulation_calculator', 'OvulationCalculator', 'health', 'Ovulation Calculator', schema([
    "            \$this->field('lmp_date', 'Last Period Start', 'date', ['default' => '".date('Y-m-d', strtotime('-7 days'))."']),",
    num('cycle_length', 'Average Cycle Length', ['default' => 28, 'min' => 21, 'max' => 45, 'step' => 1]),
]), <<<'CODE'
        $lmp = \Carbon\Carbon::parse($this->toString($inputs, 'lmp_date'));
        $cycle = (int) $this->requireNumeric($inputs, 'cycle_length');
        $ovulation = $lmp->copy()->addDays($cycle - 14);
        $fertileStart = $ovulation->copy()->subDays(5);
        $fertileEnd = $ovulation->copy()->addDay();
        $nextPeriod = $lmp->copy()->addDays($cycle);
        return [
            'results' => [
                'estimated_ovulation' => $ovulation->toDateString(),
                'fertile_window' => $fertileStart->toDateString().' to '.$fertileEnd->toDateString(),
                'next_period' => $nextPeriod->toDateString(),
            ],
            'breakdown' => ['cycle_length' => $cycle],
            'units' => ['estimated_ovulation' => 'date', 'fertile_window' => 'dates', 'next_period' => 'date'],
        ];
CODE);

$part3[] = item('pregnancy_week_calculator', 'PregnancyWeekCalculator', 'health', 'Pregnancy Week Calculator', schema([
    "            \$this->field('lmp_date', 'LMP Date', 'date', ['default' => '".date('Y-m-d', strtotime('-12 weeks'))."']),",
]), <<<'CODE'
        $lmp = \Carbon\Carbon::parse($this->toString($inputs, 'lmp_date'));
        $days = max(0, $lmp->diffInDays(now()));
        $weeks = intdiv($days, 7);
        $rem = $days % 7;
        return [
            'results' => [
                'gestational_age' => "{$weeks} weeks + {$rem} days",
                'trimester' => $weeks < 13 ? 1 : ($weeks < 27 ? 2 : 3),
            ],
            'breakdown' => ['days_since_lmp' => $days],
            'units' => ['gestational_age' => 'weeks+days', 'trimester' => '1-3'],
        ];
CODE);

$part3[] = item('heart_rate_zone_calculator', 'HeartRateZoneCalculator', 'fitness', 'Heart Rate Zone Calculator', schema([
    num('age', 'Age', ['default' => 30, 'min' => 10, 'max' => 90, 'step' => 1]),
    num('resting_hr', 'Resting Heart Rate', ['default' => 60, 'min' => 30, 'max' => 120, 'step' => 1]),
]), <<<'CODE'
        $age = $this->requireNumeric($inputs, 'age');
        $rest = $this->requireNumeric($inputs, 'resting_hr');
        $max = 220 - $age;
        $reserve = $max - $rest;
        $zone = fn ($lo, $hi) => (int) round($rest + $reserve * $lo).'–'.(int) round($rest + $reserve * $hi);
        return [
            'results' => [
                'max_hr' => (int) $max,
                'zone_1_recovery' => $zone(0.5, 0.6),
                'zone_2_fat_burn' => $zone(0.6, 0.7),
                'zone_3_cardio' => $zone(0.7, 0.8),
                'zone_4_peak' => $zone(0.8, 0.9),
                'zone_5_max' => $zone(0.9, 1.0),
            ],
            'breakdown' => ['method' => 'Karvonen'],
            'units' => ['max_hr' => 'bpm', 'zone_1_recovery' => 'bpm', 'zone_2_fat_burn' => 'bpm', 'zone_3_cardio' => 'bpm', 'zone_4_peak' => 'bpm', 'zone_5_max' => 'bpm'],
        ];
CODE);

$part3[] = item('protein_intake_calculator', 'ProteinIntakeCalculator', 'health', 'Protein Intake Calculator', schema([
    num('weight_kg', 'Body Weight', ['default' => 70, 'unit' => 'kg', 'min' => 20, 'max' => 300]),
    sel('goal', 'Goal', ['sedentary' => 'Sedentary (0.8 g/kg)', 'active' => 'Active (1.2 g/kg)', 'muscle' => 'Muscle Gain (1.6–2.2 g/kg)', 'athlete' => 'Athlete (2.0 g/kg)'], 'active'),
]), <<<'CODE'
        $w = $this->requireNumeric($inputs, 'weight_kg');
        [$min, $max] = match ($this->toString($inputs, 'goal', 'active')) {
            'sedentary' => [0.8, 0.8],
            'muscle' => [1.6, 2.2],
            'athlete' => [2.0, 2.2],
            default => [1.2, 1.6],
        };
        return [
            'results' => [
                'protein_min_g' => $this->round($w * $min, 1),
                'protein_max_g' => $this->round($w * $max, 1),
            ],
            'breakdown' => ['weight_kg' => $w],
            'units' => ['protein_min_g' => 'g/day', 'protein_max_g' => 'g/day'],
        ];
CODE);

$part3[] = item('macro_calculator', 'MacroCalculator', 'health', 'Macro Calculator', schema([
    num('calories', 'Daily Calories', ['default' => 2000, 'unit' => 'kcal', 'min' => 800]),
    num('protein_percent', 'Protein %', ['default' => 30, 'unit' => '%', 'max' => 60]),
    num('carb_percent', 'Carb %', ['default' => 40, 'unit' => '%', 'max' => 70]),
    num('fat_percent', 'Fat %', ['default' => 30, 'unit' => '%', 'max' => 60]),
]), <<<'CODE'
        $cal = $this->requireNumeric($inputs, 'calories');
        $p = $this->requireNumeric($inputs, 'protein_percent');
        $c = $this->requireNumeric($inputs, 'carb_percent');
        $f = $this->requireNumeric($inputs, 'fat_percent');
        if (abs(($p + $c + $f) - 100) > 0.5) {
            throw new InvalidArgumentException('Macro percentages must add up to 100%.');
        }
        return [
            'results' => [
                'protein_g' => $this->round(($cal * $p / 100) / 4, 1),
                'carbs_g' => $this->round(($cal * $c / 100) / 4, 1),
                'fat_g' => $this->round(($cal * $f / 100) / 9, 1),
            ],
            'breakdown' => ['calories' => $cal],
            'units' => ['protein_g' => 'g', 'carbs_g' => 'g', 'fat_g' => 'g'],
        ];
CODE);

$part3[] = item('body_surface_area_calculator', 'BodySurfaceAreaCalculator', 'health', 'Body Surface Area Calculator', schema([
    num('height_cm', 'Height', ['default' => 170, 'unit' => 'cm', 'min' => 50]),
    num('weight_kg', 'Weight', ['default' => 70, 'unit' => 'kg', 'min' => 10]),
]), <<<'CODE'
        $h = $this->requireNumeric($inputs, 'height_cm');
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $bsa = sqrt(($h * $w) / 3600); // Mosteller
        return [
            'results' => ['bsa_m2' => $this->round($bsa, 3)],
            'breakdown' => ['formula' => 'Mosteller: √((H×W)/3600)'],
            'units' => ['bsa_m2' => 'm²'],
        ];
CODE);

// ─── Fitness ──────────────────────────────────────────────────
$part3[] = item('calories_burned_calculator', 'CaloriesBurnedCalculator', 'fitness', 'Calories Burned Calculator', schema([
    num('weight_kg', 'Weight', ['default' => 70, 'unit' => 'kg']),
    num('met', 'Activity MET', ['default' => 7, 'min' => 1, 'max' => 18, 'step' => 0.1]),
    num('minutes', 'Duration', ['default' => 30, 'unit' => 'min', 'min' => 1]),
]), <<<'CODE'
        $kcal = $this->requireNumeric($inputs, 'met') * $this->requireNumeric($inputs, 'weight_kg') * ($this->requireNumeric($inputs, 'minutes') / 60);
        return [
            'results' => ['calories_burned' => $this->round($kcal)],
            'breakdown' => ['formula' => 'kcal = MET × kg × hours'],
            'units' => ['calories_burned' => 'kcal'],
        ];
CODE);

$part3[] = item('running_pace_calculator', 'RunningPaceCalculator', 'fitness', 'Running Pace Calculator', schema([
    num('distance_km', 'Distance', ['default' => 5, 'unit' => 'km', 'min' => 0.1]),
    num('hours', 'Hours', ['default' => 0, 'min' => 0, 'max' => 24, 'step' => 1]),
    num('minutes', 'Minutes', ['default' => 28, 'min' => 0, 'max' => 59, 'step' => 1]),
    num('seconds', 'Seconds', ['default' => 0, 'min' => 0, 'max' => 59, 'step' => 1]),
]), <<<'CODE'
        $dist = $this->requireNumeric($inputs, 'distance_km');
        $totalMin = $this->requireNumeric($inputs, 'hours') * 60 + $this->requireNumeric($inputs, 'minutes') + $this->requireNumeric($inputs, 'seconds') / 60;
        $pace = $this->safeDivide($totalMin, $dist);
        $paceMin = (int) floor($pace);
        $paceSec = (int) round(($pace - $paceMin) * 60);
        $speed = $this->safeDivide($dist, $totalMin / 60);
        return [
            'results' => [
                'pace_per_km' => sprintf('%d:%02d', $paceMin, $paceSec),
                'speed_kmh' => $this->round($speed, 2),
            ],
            'breakdown' => ['total_minutes' => $this->round($totalMin, 2)],
            'units' => ['pace_per_km' => 'min/km', 'speed_kmh' => 'km/h'],
        ];
CODE);

$part3[] = item('cycling_speed_calculator', 'CyclingSpeedCalculator', 'fitness', 'Cycling Speed Calculator', schema([
    num('distance_km', 'Distance', ['default' => 40, 'unit' => 'km']),
    num('hours', 'Hours', ['default' => 1, 'min' => 0]),
    num('minutes', 'Minutes', ['default' => 30, 'min' => 0, 'max' => 59]),
]), <<<'CODE'
        $hours = $this->requireNumeric($inputs, 'hours') + $this->requireNumeric($inputs, 'minutes') / 60;
        $speed = $this->safeDivide($this->requireNumeric($inputs, 'distance_km'), $hours);
        return [
            'results' => ['average_speed_kmh' => $this->round($speed, 2)],
            'breakdown' => ['time_hours' => $this->round($hours, 3)],
            'units' => ['average_speed_kmh' => 'km/h'],
        ];
CODE);

$part3[] = item('one_rep_max_calculator', 'OneRepMaxCalculator', 'fitness', 'One Rep Max Calculator', schema([
    num('weight', 'Weight Lifted', ['default' => 80, 'unit' => 'kg']),
    num('reps', 'Reps Completed', ['default' => 5, 'min' => 1, 'max' => 12, 'step' => 1]),
]), <<<'CODE'
        $w = $this->requireNumeric($inputs, 'weight');
        $r = $this->requireNumeric($inputs, 'reps');
        $orm = $w * (1 + $r / 30); // Epley
        return [
            'results' => [
                'one_rep_max' => $this->round($orm, 1),
                'estimate_90' => $this->round($orm * 0.9, 1),
                'estimate_80' => $this->round($orm * 0.8, 1),
                'estimate_70' => $this->round($orm * 0.7, 1),
            ],
            'breakdown' => ['formula' => 'Epley: w × (1 + r/30)'],
            'units' => ['one_rep_max' => 'kg', 'estimate_90' => 'kg', 'estimate_80' => 'kg', 'estimate_70' => 'kg'],
        ];
CODE);

$part3[] = item('vo2_max_calculator', 'Vo2MaxCalculator', 'fitness', 'VO2 Max Estimator', schema([
    num('age', 'Age', ['default' => 30, 'min' => 15, 'max' => 80, 'step' => 1]),
    num('resting_hr', 'Resting Heart Rate', ['default' => 60, 'min' => 30, 'max' => 120, 'step' => 1]),
]), <<<'CODE'
        // Uth–Sørensen–Johansen estimate
        $vo2 = 15.3 * ((220 - $this->requireNumeric($inputs, 'age')) / $this->requireNumeric($inputs, 'resting_hr'));
        return [
            'results' => ['vo2_max' => $this->round($vo2, 1)],
            'breakdown' => ['method' => 'Resting HR estimate'],
            'units' => ['vo2_max' => 'mL/kg/min'],
        ];
CODE);

$part3[] = item('lean_mass_calculator', 'LeanMassCalculator', 'fitness', 'Lean Body Mass Calculator', schema([
    num('weight_kg', 'Weight', ['default' => 75, 'unit' => 'kg']),
    num('body_fat_percent', 'Body Fat %', ['default' => 20, 'unit' => '%', 'min' => 3, 'max' => 60]),
]), <<<'CODE'
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $bf = $this->requireNumeric($inputs, 'body_fat_percent');
        $fat = $w * $bf / 100;
        $lean = $w - $fat;
        return [
            'results' => ['lean_mass_kg' => $this->round($lean, 1), 'fat_mass_kg' => $this->round($fat, 1)],
            'breakdown' => ['body_fat_percent' => $bf],
            'units' => ['lean_mass_kg' => 'kg', 'fat_mass_kg' => 'kg'],
        ];
CODE);

$part3[] = item('target_heart_rate_calculator', 'TargetHeartRateCalculator', 'fitness', 'Target Heart Rate Calculator', schema([
    num('age', 'Age', ['default' => 30, 'min' => 10, 'max' => 90, 'step' => 1]),
]), <<<'CODE'
        $max = 220 - $this->requireNumeric($inputs, 'age');
        return [
            'results' => [
                'max_hr' => (int) $max,
                'moderate_zone' => (int) round($max * 0.5).'–'.(int) round($max * 0.7),
                'vigorous_zone' => (int) round($max * 0.7).'–'.(int) round($max * 0.85),
            ],
            'breakdown' => ['formula' => '220 − age'],
            'units' => ['max_hr' => 'bpm', 'moderate_zone' => 'bpm', 'vigorous_zone' => 'bpm'],
        ];
CODE);

$part3[] = item('tdee_calculator', 'TdeeCalculator', 'fitness', 'TDEE Calculator', schema([
    num('weight_kg', 'Weight', ['default' => 70, 'unit' => 'kg']),
    num('height_cm', 'Height', ['default' => 170, 'unit' => 'cm']),
    num('age', 'Age', ['default' => 30, 'min' => 15, 'max' => 90, 'step' => 1]),
    sel('gender', 'Gender', ['male' => 'Male', 'female' => 'Female'], 'male'),
    sel('activity', 'Activity Level', [
        '1.2' => 'Sedentary',
        '1.375' => 'Lightly active',
        '1.55' => 'Moderately active',
        '1.725' => 'Very active',
        '1.9' => 'Extra active',
    ], '1.55'),
]), <<<'CODE'
        $w = $this->requireNumeric($inputs, 'weight_kg');
        $h = $this->requireNumeric($inputs, 'height_cm');
        $age = $this->requireNumeric($inputs, 'age');
        $bmr = $this->toString($inputs, 'gender', 'male') === 'female'
            ? (10 * $w) + (6.25 * $h) - (5 * $age) - 161
            : (10 * $w) + (6.25 * $h) - (5 * $age) + 5;
        $factor = (float) $this->toString($inputs, 'activity', '1.55');
        $tdee = $bmr * $factor;
        return [
            'results' => [
                'bmr' => $this->round($bmr),
                'tdee' => $this->round($tdee),
                'cut_calories' => $this->round($tdee - 500),
                'bulk_calories' => $this->round($tdee + 300),
            ],
            'breakdown' => ['formula' => 'Mifflin–St Jeor × activity'],
            'units' => ['bmr' => 'kcal', 'tdee' => 'kcal', 'cut_calories' => 'kcal', 'bulk_calories' => 'kcal'],
        ];
CODE);

// ─── Education ────────────────────────────────────────────────
$part3[] = item('grade_calculator', 'GradeCalculator', 'education', 'Grade Calculator', schema([
    num('score', 'Score Obtained', ['default' => 85, 'min' => 0]),
    num('total', 'Total Marks', ['default' => 100, 'min' => 1]),
]), <<<'CODE'
        $score = $this->requireNumeric($inputs, 'score');
        $total = $this->requireNumeric($inputs, 'total');
        $pct = $this->percentageOf($score, $total);
        $letter = match (true) {
            $pct >= 90 => 'A+',
            $pct >= 80 => 'A',
            $pct >= 70 => 'B',
            $pct >= 60 => 'C',
            $pct >= 50 => 'D',
            default => 'F',
        };
        return [
            'results' => ['percentage' => $this->round($pct, 2), 'letter_grade' => $letter],
            'breakdown' => ['score' => $score, 'total' => $total],
            'units' => ['percentage' => '%', 'letter_grade' => 'grade'],
        ];
CODE);

$part3[] = item('marks_calculator', 'MarksCalculator', 'education', 'Marks Calculator', schema([
    num('obtained', 'Marks Obtained', ['default' => 420, 'min' => 0]),
    num('maximum', 'Maximum Marks', ['default' => 500, 'min' => 1]),
]), <<<'CODE'
        $got = $this->requireNumeric($inputs, 'obtained');
        $max = $this->requireNumeric($inputs, 'maximum');
        return [
            'results' => [
                'percentage' => $this->round($this->percentageOf($got, $max), 2),
                'marks_needed_for_90' => max(0, $this->round(($max * 0.9) - $got, 1)),
            ],
            'breakdown' => ['obtained' => $got, 'maximum' => $max],
            'units' => ['percentage' => '%', 'marks_needed_for_90' => 'marks'],
        ];
CODE);

$part3[] = item('attendance_calculator', 'AttendanceCalculator', 'education', 'Attendance Calculator', schema([
    num('classes_held', 'Classes Held', ['default' => 50, 'min' => 1, 'step' => 1]),
    num('classes_attended', 'Classes Attended', ['default' => 40, 'min' => 0, 'step' => 1]),
    num('required_percent', 'Required %', ['default' => 75, 'unit' => '%', 'min' => 1, 'max' => 100]),
]), <<<'CODE'
        $held = $this->requireNumeric($inputs, 'classes_held');
        $attended = $this->requireNumeric($inputs, 'classes_attended');
        $req = $this->requireNumeric($inputs, 'required_percent');
        $current = $this->percentageOf($attended, $held);
        $needTotal = ceil($held * $req / 100);
        $needMore = max(0, $needTotal - $attended);
        return [
            'results' => [
                'current_attendance' => $this->round($current, 2),
                'classes_needed_more' => (int) $needMore,
            ],
            'breakdown' => ['required_percent' => $req],
            'units' => ['current_attendance' => '%', 'classes_needed_more' => 'classes'],
        ];
CODE);

$part3[] = item('study_time_calculator', 'StudyTimeCalculator', 'education', 'Study Time Calculator', schema([
    num('pages', 'Pages To Study', ['default' => 40, 'min' => 1, 'step' => 1]),
    num('pages_per_hour', 'Pages Per Hour', ['default' => 8, 'min' => 0.5]),
    num('days_available', 'Days Available', ['default' => 5, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $pages = $this->requireNumeric($inputs, 'pages');
        $pph = $this->requireNumeric($inputs, 'pages_per_hour');
        $days = $this->requireNumeric($inputs, 'days_available');
        $hours = $this->safeDivide($pages, $pph);
        return [
            'results' => [
                'total_study_hours' => $this->round($hours, 2),
                'hours_per_day' => $this->round($this->safeDivide($hours, $days), 2),
            ],
            'breakdown' => ['pages' => $pages],
            'units' => ['total_study_hours' => 'hours', 'hours_per_day' => 'hours/day'],
        ];
CODE);

return $part3;
