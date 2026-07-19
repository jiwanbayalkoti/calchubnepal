<?php

/**
 * Remaining calculator definitions (construction → nepal).
 * Merged by generate_missing_calculators.php
 */

require_once __DIR__.'/catalog_helpers.php';

$more = [];

// ─── Construction ─────────────────────────────────────────────
$more[] = item('pcc_calculator', 'PccCalculator', 'construction', 'PCC Calculator', schema([
    num('length', 'Length', ['default' => 10, 'unit' => 'm']),
    num('width', 'Width', ['default' => 10, 'unit' => 'm']),
    num('thickness', 'Thickness', ['default' => 0.1, 'unit' => 'm', 'min' => 0.01]),
    sel('mix', 'Mix Ratio', ['1:3:6' => '1:3:6', '1:4:8' => '1:4:8', '1:5:10' => '1:5:10'], '1:4:8'),
]), <<<'CODE'
        $volume = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'thickness');
        $wet = $volume * 1.52;
        [$c, $s, $a] = array_map('intval', explode(':', $this->toString($inputs, 'mix', '1:4:8')));
        $parts = $c + $s + $a;
        $cementM3 = $wet * $c / $parts;
        $bags = $cementM3 / 0.035;
        return [
            'results' => [
                'volume_m3' => $this->round($volume, 3),
                'cement_bags' => $this->round($bags, 1),
                'sand_m3' => $this->round($wet * $s / $parts, 3),
                'aggregate_m3' => $this->round($wet * $a / $parts, 3),
            ],
            'breakdown' => ['wet_volume' => $this->round($wet, 3), 'mix' => "{$c}:{$s}:{$a}"],
            'units' => ['volume_m3' => 'm³', 'cement_bags' => 'bags', 'sand_m3' => 'm³', 'aggregate_m3' => 'm³'],
        ];
CODE);

$more[] = item('rcc_calculator', 'RccCalculator', 'construction', 'RCC Calculator', schema([
    num('length', 'Length', ['default' => 5, 'unit' => 'm']),
    num('width', 'Width', ['default' => 0.3, 'unit' => 'm']),
    num('depth', 'Depth', ['default' => 0.45, 'unit' => 'm']),
    sel('mix', 'Mix', ['1:1.5:3' => 'M20 (1:1.5:3)', '1:1:2' => 'M25 (1:1:2)', '1:2:4' => 'M15 (1:2:4)'], '1:1.5:3'),
]), <<<'CODE'
        $volume = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'depth');
        $wet = $volume * 1.54;
        $parts = array_map('floatval', explode(':', $this->toString($inputs, 'mix', '1:1.5:3')));
        $sum = array_sum($parts);
        $cementM3 = $wet * $parts[0] / $sum;
        return [
            'results' => [
                'concrete_volume_m3' => $this->round($volume, 3),
                'cement_bags' => $this->round($cementM3 / 0.035, 1),
                'sand_m3' => $this->round($wet * $parts[1] / $sum, 3),
                'aggregate_m3' => $this->round($wet * $parts[2] / $sum, 3),
            ],
            'breakdown' => ['wet_volume' => $this->round($wet, 3)],
            'units' => ['concrete_volume_m3' => 'm³', 'cement_bags' => 'bags', 'sand_m3' => 'm³', 'aggregate_m3' => 'm³'],
        ];
CODE);

$more[] = item('stair_calculator', 'StairCalculator', 'construction', 'Stair Calculator', schema([
    num('total_rise', 'Total Rise (Height)', ['default' => 3.0, 'unit' => 'm', 'min' => 0.5]),
    num('riser_height', 'Preferred Riser Height', ['default' => 0.15, 'unit' => 'm', 'min' => 0.1, 'max' => 0.2]),
    num('tread_depth', 'Tread Depth', ['default' => 0.28, 'unit' => 'm', 'min' => 0.2]),
]), <<<'CODE'
        $rise = $this->requireNumeric($inputs, 'total_rise');
        $riser = $this->requireNumeric($inputs, 'riser_height');
        $tread = $this->requireNumeric($inputs, 'tread_depth');
        $risers = (int) max(1, round($rise / $riser));
        $actualRiser = $rise / $risers;
        $treads = max(1, $risers - 1);
        $run = $treads * $tread;
        return [
            'results' => [
                'number_of_risers' => $risers,
                'actual_riser_height' => $this->round($actualRiser, 3),
                'number_of_treads' => $treads,
                'total_run' => $this->round($run, 3),
            ],
            'breakdown' => ['rule_of_thumb' => '2R + T ≈ 0.60–0.65 m'],
            'units' => ['number_of_risers' => 'count', 'actual_riser_height' => 'm', 'number_of_treads' => 'count', 'total_run' => 'm'],
        ];
CODE);

$more[] = item('marble_calculator', 'MarbleCalculator', 'construction', 'Marble Calculator', schema([
    num('area', 'Floor/Wall Area', ['default' => 20, 'unit' => 'm²', 'min' => 0.1]),
    num('tile_length', 'Marble Length', ['default' => 0.6, 'unit' => 'm', 'min' => 0.1]),
    num('tile_width', 'Marble Width', ['default' => 0.6, 'unit' => 'm', 'min' => 0.1]),
    num('wastage', 'Wastage', ['default' => 10, 'unit' => '%', 'max' => 30]),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'area');
        $tileArea = $this->requireNumeric($inputs, 'tile_length') * $this->requireNumeric($inputs, 'tile_width');
        $wastage = $this->requireNumeric($inputs, 'wastage');
        $needed = $this->safeDivide($area, $tileArea) * (1 + $wastage / 100);
        return [
            'results' => ['pieces_required' => (int) ceil($needed), 'marble_area_with_wastage' => $this->round($area * (1 + $wastage / 100), 2)],
            'breakdown' => ['piece_area' => $this->round($tileArea, 4)],
            'units' => ['pieces_required' => 'pcs', 'marble_area_with_wastage' => 'm²'],
        ];
CODE);

$more[] = item('wallpaper_calculator', 'WallpaperCalculator', 'construction', 'Wallpaper Calculator', schema([
    num('wall_area', 'Wall Area', ['default' => 40, 'unit' => 'm²']),
    num('roll_coverage', 'Coverage Per Roll', ['default' => 5, 'unit' => 'm²', 'min' => 0.5]),
    num('wastage', 'Wastage', ['default' => 15, 'unit' => '%', 'max' => 30]),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'wall_area') * (1 + $this->requireNumeric($inputs, 'wastage') / 100);
        $coverage = $this->requireNumeric($inputs, 'roll_coverage');
        $rolls = ceil($this->safeDivide($area, $coverage));
        return [
            'results' => ['rolls_needed' => (int) $rolls, 'adjusted_area' => $this->round($area, 2)],
            'breakdown' => ['coverage_per_roll' => $coverage],
            'units' => ['rolls_needed' => 'rolls', 'adjusted_area' => 'm²'],
        ];
CODE);

$more[] = item('rebar_calculator', 'RebarCalculator', 'construction', 'Rebar Calculator', schema([
    num('length', 'Bar Length', ['default' => 12, 'unit' => 'm']),
    num('diameter_mm', 'Diameter', ['default' => 12, 'unit' => 'mm', 'min' => 6, 'max' => 40]),
    num('quantity', 'Number of Bars', ['default' => 10, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $length = $this->requireNumeric($inputs, 'length');
        $d = $this->requireNumeric($inputs, 'diameter_mm');
        $qty = $this->requireNumeric($inputs, 'quantity');
        // weight kg/m ≈ d²/162
        $kgPerM = ($d ** 2) / 162;
        $totalLength = $length * $qty;
        $weight = $totalLength * $kgPerM;
        return [
            'results' => [
                'weight_per_meter' => $this->round($kgPerM, 3),
                'total_length' => $this->round($totalLength, 2),
                'total_weight' => $this->round($weight, 2),
            ],
            'breakdown' => ['formula' => 'kg/m = d² / 162'],
            'units' => ['weight_per_meter' => 'kg/m', 'total_length' => 'm', 'total_weight' => 'kg'],
        ];
CODE);

$more[] = item('flooring_calculator', 'FlooringCalculator', 'construction', 'Flooring Calculator', schema([
    num('length', 'Room Length', ['default' => 4, 'unit' => 'm']),
    num('width', 'Room Width', ['default' => 3.5, 'unit' => 'm']),
    num('price_per_m2', 'Price Per m²', ['default' => 800, 'unit' => 'currency', 'min' => 0]),
    num('wastage', 'Wastage', ['default' => 5, 'unit' => '%', 'max' => 20]),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        $withWaste = $area * (1 + $this->requireNumeric($inputs, 'wastage') / 100);
        $cost = $withWaste * $this->requireNumeric($inputs, 'price_per_m2');
        return [
            'results' => [
                'floor_area' => $this->round($area, 2),
                'material_area' => $this->round($withWaste, 2),
                'estimated_cost' => $this->round($cost),
            ],
            'breakdown' => ['wastage_percent' => $this->requireNumeric($inputs, 'wastage')],
            'units' => ['floor_area' => 'm²', 'material_area' => 'm²', 'estimated_cost' => 'currency'],
        ];
CODE);

$more[] = item('roofing_calculator', 'RoofingCalculator', 'construction', 'Roofing Calculator', schema([
    num('length', 'Roof Length', ['default' => 10, 'unit' => 'm']),
    num('width', 'Roof Width', ['default' => 8, 'unit' => 'm']),
    num('pitch_factor', 'Pitch Factor', ['default' => 1.15, 'min' => 1, 'max' => 2, 'step' => 0.01]),
    num('sheet_coverage', 'Sheet Coverage', ['default' => 1.8, 'unit' => 'm²', 'min' => 0.5]),
]), <<<'CODE'
        $plan = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width');
        $area = $plan * $this->requireNumeric($inputs, 'pitch_factor');
        $sheets = ceil($this->safeDivide($area, $this->requireNumeric($inputs, 'sheet_coverage')));
        return [
            'results' => ['roof_area' => $this->round($area, 2), 'sheets_needed' => (int) $sheets],
            'breakdown' => ['plan_area' => $this->round($plan, 2)],
            'units' => ['roof_area' => 'm²', 'sheets_needed' => 'sheets'],
        ];
CODE);

$more[] = item('water_tank_calculator', 'WaterTankCalculator', 'construction', 'Water Tank Calculator', schema([
    num('length', 'Length', ['default' => 2, 'unit' => 'm']),
    num('width', 'Width', ['default' => 1.5, 'unit' => 'm']),
    num('height', 'Height', ['default' => 1.2, 'unit' => 'm']),
]), <<<'CODE'
        $volumeM3 = $this->requireNumeric($inputs, 'length') * $this->requireNumeric($inputs, 'width') * $this->requireNumeric($inputs, 'height');
        $liters = $volumeM3 * 1000;
        return [
            'results' => ['volume_m3' => $this->round($volumeM3, 3), 'capacity_liters' => $this->round($liters, 1)],
            'breakdown' => ['shape' => 'rectangular'],
            'units' => ['volume_m3' => 'm³', 'capacity_liters' => 'L'],
        ];
CODE);

$more[] = item('septic_tank_calculator', 'SepticTankCalculator', 'construction', 'Septic Tank Calculator', schema([
    num('users', 'Number of Users', ['default' => 5, 'min' => 1, 'step' => 1]),
    num('liters_per_person', 'Liters Per Person / Day', ['default' => 100, 'min' => 50, 'max' => 300]),
    num('retention_days', 'Retention Days', ['default' => 3, 'min' => 1, 'max' => 10, 'step' => 1]),
]), <<<'CODE'
        $users = $this->requireNumeric($inputs, 'users');
        $lpp = $this->requireNumeric($inputs, 'liters_per_person');
        $days = $this->requireNumeric($inputs, 'retention_days');
        $liters = $users * $lpp * $days;
        return [
            'results' => [
                'tank_capacity_liters' => $this->round($liters),
                'tank_capacity_m3' => $this->round($liters / 1000, 3),
            ],
            'breakdown' => ['daily_flow_liters' => $this->round($users * $lpp)],
            'units' => ['tank_capacity_liters' => 'L', 'tank_capacity_m3' => 'm³'],
        ];
CODE);

$more[] = item('house_cost_calculator', 'HouseCostCalculator', 'construction', 'House Cost Calculator', schema([
    num('built_up_area', 'Built-up Area', ['default' => 1500, 'unit' => 'sq.ft']),
    num('cost_per_sqft', 'Construction Cost / sq.ft', ['default' => 2500, 'unit' => 'currency']),
    num('finishing_percent', 'Finishing Contingency', ['default' => 15, 'unit' => '%', 'max' => 50]),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'built_up_area');
        $rate = $this->requireNumeric($inputs, 'cost_per_sqft');
        $extra = $this->requireNumeric($inputs, 'finishing_percent');
        $base = $area * $rate;
        $total = $base * (1 + $extra / 100);
        return [
            'results' => [
                'base_cost' => $this->round($base),
                'estimated_total_cost' => $this->round($total),
                'cost_per_sqft_effective' => $this->round($this->safeDivide($total, $area)),
            ],
            'breakdown' => ['built_up_area' => $area],
            'units' => ['base_cost' => 'currency', 'estimated_total_cost' => 'currency', 'cost_per_sqft_effective' => 'currency'],
        ];
CODE);

$more[] = item('boq_calculator', 'BoqCalculator', 'construction', 'BOQ Estimator', schema([
    num('quantity', 'Quantity', ['default' => 100]),
    num('unit_rate', 'Unit Rate', ['default' => 50, 'unit' => 'currency']),
    num('wastage', 'Wastage / Contingency', ['default' => 5, 'unit' => '%', 'max' => 25]),
]), <<<'CODE'
        $qty = $this->requireNumeric($inputs, 'quantity');
        $rate = $this->requireNumeric($inputs, 'unit_rate');
        $waste = $this->requireNumeric($inputs, 'wastage');
        $adjustedQty = $qty * (1 + $waste / 100);
        $amount = $adjustedQty * $rate;
        return [
            'results' => [
                'adjusted_quantity' => $this->round($adjustedQty, 2),
                'line_amount' => $this->round($amount),
            ],
            'breakdown' => ['base_quantity' => $qty, 'unit_rate' => $rate],
            'units' => ['adjusted_quantity' => 'qty', 'line_amount' => 'currency'],
        ];
CODE);

// ─── Engineering ──────────────────────────────────────────────
$more[] = item('pipe_volume_calculator', 'PipeVolumeCalculator', 'engineering', 'Pipe Volume Calculator', schema([
    num('diameter', 'Inner Diameter', ['default' => 0.1, 'unit' => 'm', 'min' => 0.001]),
    num('length', 'Length', ['default' => 10, 'unit' => 'm']),
]), <<<'CODE'
        $r = $this->requireNumeric($inputs, 'diameter') / 2;
        $length = $this->requireNumeric($inputs, 'length');
        $volume = pi() * ($r ** 2) * $length;
        return [
            'results' => ['volume_m3' => $this->round($volume, 6), 'volume_liters' => $this->round($volume * 1000, 2)],
            'breakdown' => ['radius_m' => $this->round($r, 4)],
            'units' => ['volume_m3' => 'm³', 'volume_liters' => 'L'],
        ];
CODE);

$more[] = item('pipe_flow_calculator', 'PipeFlowCalculator', 'engineering', 'Pipe Flow Calculator', schema([
    num('diameter', 'Diameter', ['default' => 0.05, 'unit' => 'm', 'min' => 0.001]),
    num('velocity', 'Flow Velocity', ['default' => 1.5, 'unit' => 'm/s', 'min' => 0.01]),
]), <<<'CODE'
        $area = pi() * (($this->requireNumeric($inputs, 'diameter') / 2) ** 2);
        $q = $area * $this->requireNumeric($inputs, 'velocity');
        return [
            'results' => [
                'flow_m3_per_s' => $this->round($q, 6),
                'flow_liters_per_s' => $this->round($q * 1000, 3),
                'flow_m3_per_hour' => $this->round($q * 3600, 3),
            ],
            'breakdown' => ['cross_section_m2' => $this->round($area, 6)],
            'units' => ['flow_m3_per_s' => 'm³/s', 'flow_liters_per_s' => 'L/s', 'flow_m3_per_hour' => 'm³/h'],
        ];
CODE);

$more[] = item('pressure_drop_calculator', 'PressureDropCalculator', 'engineering', 'Pressure Drop Estimator', schema([
    num('friction_factor', 'Friction Factor f', ['default' => 0.02, 'min' => 0.001, 'max' => 0.1, 'step' => 0.001]),
    num('length', 'Pipe Length', ['default' => 50, 'unit' => 'm']),
    num('diameter', 'Diameter', ['default' => 0.05, 'unit' => 'm', 'min' => 0.001]),
    num('velocity', 'Velocity', ['default' => 2, 'unit' => 'm/s']),
    num('density', 'Fluid Density', ['default' => 1000, 'unit' => 'kg/m³']),
]), <<<'CODE'
        // Darcy-Weisbach: ΔP = f * (L/D) * (ρ v² / 2)
        $f = $this->requireNumeric($inputs, 'friction_factor');
        $L = $this->requireNumeric($inputs, 'length');
        $D = $this->requireNumeric($inputs, 'diameter');
        $v = $this->requireNumeric($inputs, 'velocity');
        $rho = $this->requireNumeric($inputs, 'density');
        $dp = $f * ($L / $D) * ($rho * ($v ** 2) / 2);
        return [
            'results' => ['pressure_drop_pa' => $this->round($dp), 'pressure_drop_bar' => $this->round($dp / 100000, 4)],
            'breakdown' => ['formula' => 'Darcy–Weisbach'],
            'units' => ['pressure_drop_pa' => 'Pa', 'pressure_drop_bar' => 'bar'],
        ];
CODE);

$more[] = item('torque_calculator', 'TorqueCalculator', 'engineering', 'Torque Calculator', schema([
    num('force', 'Force', ['default' => 100, 'unit' => 'N']),
    num('lever_arm', 'Lever Arm', ['default' => 0.5, 'unit' => 'm', 'min' => 0.001]),
]), <<<'CODE'
        $torque = $this->requireNumeric($inputs, 'force') * $this->requireNumeric($inputs, 'lever_arm');
        return [
            'results' => ['torque' => $this->round($torque, 3)],
            'breakdown' => ['formula' => 'τ = F × r'],
            'units' => ['torque' => 'N·m'],
        ];
CODE);

$more[] = item('horsepower_calculator', 'HorsepowerCalculator', 'engineering', 'Horsepower Calculator', schema([
    num('torque_nm', 'Torque', ['default' => 200, 'unit' => 'N·m']),
    num('rpm', 'RPM', ['default' => 3000, 'min' => 1]),
]), <<<'CODE'
        $hp = ($this->requireNumeric($inputs, 'torque_nm') * $this->requireNumeric($inputs, 'rpm')) / 7127;
        $kw = $hp * 0.7457;
        return [
            'results' => ['horsepower' => $this->round($hp, 3), 'kilowatts' => $this->round($kw, 3)],
            'breakdown' => ['formula' => 'HP = τ × RPM / 7127'],
            'units' => ['horsepower' => 'HP', 'kilowatts' => 'kW'],
        ];
CODE);

$more[] = item('rpm_calculator', 'RpmCalculator', 'engineering', 'RPM Calculator', schema([
    num('speed', 'Linear Speed', ['default' => 10, 'unit' => 'm/s']),
    num('diameter', 'Wheel/Pulley Diameter', ['default' => 0.5, 'unit' => 'm', 'min' => 0.001]),
]), <<<'CODE'
        $circumference = pi() * $this->requireNumeric($inputs, 'diameter');
        $rps = $this->safeDivide($this->requireNumeric($inputs, 'speed'), $circumference);
        return [
            'results' => ['rpm' => $this->round($rps * 60, 2), 'rps' => $this->round($rps, 4)],
            'breakdown' => ['circumference_m' => $this->round($circumference, 4)],
            'units' => ['rpm' => 'RPM', 'rps' => 'rev/s'],
        ];
CODE);

$more[] = item('ohms_law_calculator', 'OhmsLawCalculator', 'engineering', "Ohm's Law Calculator", schema([
    sel('solve_for', 'Solve For', ['voltage' => 'Voltage (V)', 'current' => 'Current (I)', 'resistance' => 'Resistance (R)', 'power' => 'Power (P)'], 'voltage'),
    num('voltage', 'Voltage (V)', ['default' => 12, 'required' => false, 'min' => 0]),
    num('current', 'Current (A)', ['default' => 2, 'required' => false, 'min' => 0]),
    num('resistance', 'Resistance (Ω)', ['default' => 6, 'required' => false, 'min' => 0]),
    num('power', 'Power (W)', ['default' => 24, 'required' => false, 'min' => 0]),
]), <<<'CODE'
        $mode = $this->toString($inputs, 'solve_for', 'voltage');
        $v = $this->toFloat($inputs, 'voltage');
        $i = $this->toFloat($inputs, 'current');
        $r = $this->toFloat($inputs, 'resistance');
        $p = $this->toFloat($inputs, 'power');
        $result = match ($mode) {
            'current' => $this->safeDivide($v, $r),
            'resistance' => $this->safeDivide($v, $i),
            'power' => $v * $i,
            default => $i * $r,
        };
        $label = match ($mode) {
            'current' => 'current_a',
            'resistance' => 'resistance_ohm',
            'power' => 'power_w',
            default => 'voltage_v',
        };
        return [
            'results' => [$label => $this->round($result, 4)],
            'breakdown' => ['solve_for' => $mode],
            'units' => [$label => match ($mode) { 'current' => 'A', 'resistance' => 'Ω', 'power' => 'W', default => 'V' }],
        ];
CODE);

$more[] = item('watt_calculator', 'WattCalculator', 'engineering', 'Watt Calculator', schema([
    num('voltage', 'Voltage', ['default' => 220, 'unit' => 'V']),
    num('current', 'Current', ['default' => 5, 'unit' => 'A']),
    num('power_factor', 'Power Factor', ['default' => 1, 'min' => 0.1, 'max' => 1, 'step' => 0.01]),
]), <<<'CODE'
        $watts = $this->requireNumeric($inputs, 'voltage') * $this->requireNumeric($inputs, 'current') * $this->requireNumeric($inputs, 'power_factor');
        return [
            'results' => ['watts' => $this->round($watts, 2), 'kilowatts' => $this->round($watts / 1000, 4)],
            'breakdown' => ['formula' => 'P = V × I × PF'],
            'units' => ['watts' => 'W', 'kilowatts' => 'kW'],
        ];
CODE);

$more[] = item('kva_calculator', 'KvaCalculator', 'engineering', 'kVA Calculator', schema([
    num('kw', 'Real Power', ['default' => 10, 'unit' => 'kW']),
    num('power_factor', 'Power Factor', ['default' => 0.8, 'min' => 0.1, 'max' => 1, 'step' => 0.01]),
]), <<<'CODE'
        $kw = $this->requireNumeric($inputs, 'kw');
        $pf = $this->requireNumeric($inputs, 'power_factor');
        $kva = $this->safeDivide($kw, $pf);
        return [
            'results' => ['kva' => $this->round($kva, 3), 'kvar_approx' => $this->round(sqrt(max(($kva ** 2) - ($kw ** 2), 0)), 3)],
            'breakdown' => ['formula' => 'kVA = kW / PF'],
            'units' => ['kva' => 'kVA', 'kvar_approx' => 'kVAR'],
        ];
CODE);

$more[] = item('transformer_calculator', 'TransformerCalculator', 'engineering', 'Transformer Calculator', schema([
    num('primary_voltage', 'Primary Voltage', ['default' => 11000, 'unit' => 'V']),
    num('secondary_voltage', 'Secondary Voltage', ['default' => 415, 'unit' => 'V']),
    num('primary_turns', 'Primary Turns (optional)', ['default' => 1000, 'required' => false, 'min' => 1]),
]), <<<'CODE'
        $vp = $this->requireNumeric($inputs, 'primary_voltage');
        $vs = $this->requireNumeric($inputs, 'secondary_voltage');
        $np = $this->toFloat($inputs, 'primary_turns', 0);
        $ratio = $this->safeDivide($vp, $vs);
        $results = ['turns_ratio' => $this->round($ratio, 4)];
        if ($np > 0) {
            $results['secondary_turns'] = (int) round($np / $ratio);
        }
        return [
            'results' => $results,
            'breakdown' => ['formula' => 'Vp/Vs = Np/Ns'],
            'units' => ['turns_ratio' => 'ratio', 'secondary_turns' => 'turns'],
        ];
CODE);

$more[] = item('cable_size_calculator', 'CableSizeCalculator', 'engineering', 'Cable Size Estimator', schema([
    num('current', 'Load Current', ['default' => 32, 'unit' => 'A', 'min' => 1]),
    num('length', 'Cable Length', ['default' => 30, 'unit' => 'm']),
    num('voltage', 'System Voltage', ['default' => 230, 'unit' => 'V']),
    num('max_drop_percent', 'Max Voltage Drop', ['default' => 3, 'unit' => '%', 'max' => 10]),
]), <<<'CODE'
        // Simplified copper: A (mm²) ≈ (2 × L × I × 0.0175) / (V × drop%)
        $i = $this->requireNumeric($inputs, 'current');
        $l = $this->requireNumeric($inputs, 'length');
        $v = $this->requireNumeric($inputs, 'voltage');
        $drop = $this->requireNumeric($inputs, 'max_drop_percent');
        $area = (2 * $l * $i * 0.0175) / ($v * ($drop / 100));
        $standard = [1.5, 2.5, 4, 6, 10, 16, 25, 35, 50, 70, 95];
        $suggested = end($standard);
        foreach ($standard as $size) {
            if ($size >= $area) {
                $suggested = $size;
                break;
            }
        }
        return [
            'results' => [
                'minimum_area_mm2' => $this->round($area, 2),
                'suggested_cable_mm2' => $suggested,
            ],
            'breakdown' => ['note' => 'Approximate copper sizing — verify with local electrical codes'],
            'units' => ['minimum_area_mm2' => 'mm²', 'suggested_cable_mm2' => 'mm²'],
        ];
CODE);

$more[] = item('solar_panel_calculator', 'SolarPanelCalculator', 'engineering', 'Solar Panel Calculator', schema([
    num('daily_kwh', 'Daily Energy Need', ['default' => 10, 'unit' => 'kWh']),
    num('sun_hours', 'Peak Sun Hours', ['default' => 4.5, 'min' => 1, 'max' => 8]),
    num('panel_watt', 'Panel Wattage', ['default' => 400, 'unit' => 'W', 'min' => 50]),
    num('system_efficiency', 'System Efficiency', ['default' => 80, 'unit' => '%', 'min' => 50, 'max' => 100]),
]), <<<'CODE'
        $need = $this->requireNumeric($inputs, 'daily_kwh');
        $sun = $this->requireNumeric($inputs, 'sun_hours');
        $panelW = $this->requireNumeric($inputs, 'panel_watt');
        $eff = $this->requireNumeric($inputs, 'system_efficiency') / 100;
        $requiredKw = $this->safeDivide($need, $sun * $eff);
        $panels = ceil(($requiredKw * 1000) / $panelW);
        return [
            'results' => [
                'required_array_kw' => $this->round($requiredKw, 2),
                'panels_needed' => (int) $panels,
            ],
            'breakdown' => ['daily_kwh' => $need, 'peak_sun_hours' => $sun],
            'units' => ['required_array_kw' => 'kW', 'panels_needed' => 'panels'],
        ];
CODE);

$more[] = item('battery_backup_calculator', 'BatteryBackupCalculator', 'engineering', 'Battery Backup Calculator', schema([
    num('load_watts', 'Load', ['default' => 500, 'unit' => 'W']),
    num('backup_hours', 'Backup Hours', ['default' => 4, 'min' => 0.5]),
    num('battery_voltage', 'Battery Voltage', ['default' => 12, 'unit' => 'V']),
    num('dod', 'Depth of Discharge', ['default' => 50, 'unit' => '%', 'min' => 20, 'max' => 100]),
]), <<<'CODE'
        $wh = $this->requireNumeric($inputs, 'load_watts') * $this->requireNumeric($inputs, 'backup_hours');
        $v = $this->requireNumeric($inputs, 'battery_voltage');
        $dod = $this->requireNumeric($inputs, 'dod') / 100;
        $ah = $this->safeDivide($wh, $v * $dod);
        return [
            'results' => [
                'energy_wh' => $this->round($wh),
                'battery_ah_required' => $this->round($ah, 1),
            ],
            'breakdown' => ['formula' => 'Ah = (W × h) / (V × DoD)'],
            'units' => ['energy_wh' => 'Wh', 'battery_ah_required' => 'Ah'],
        ];
CODE);

$more[] = item('ups_calculator', 'UpsCalculator', 'engineering', 'UPS Size Calculator', schema([
    num('load_watts', 'Total Load', ['default' => 800, 'unit' => 'W']),
    num('power_factor', 'Power Factor', ['default' => 0.8, 'min' => 0.5, 'max' => 1, 'step' => 0.01]),
    num('headroom_percent', 'Headroom', ['default' => 25, 'unit' => '%', 'max' => 50]),
]), <<<'CODE'
        $watts = $this->requireNumeric($inputs, 'load_watts');
        $pf = $this->requireNumeric($inputs, 'power_factor');
        $head = 1 + $this->requireNumeric($inputs, 'headroom_percent') / 100;
        $va = $this->safeDivide($watts, $pf) * $head;
        return [
            'results' => ['recommended_ups_va' => $this->round($va), 'recommended_ups_kva' => $this->round($va / 1000, 2)],
            'breakdown' => ['load_watts' => $watts],
            'units' => ['recommended_ups_va' => 'VA', 'recommended_ups_kva' => 'kVA'],
        ];
CODE);

return $more;
