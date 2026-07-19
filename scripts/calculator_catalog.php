<?php

/**
 * Catalog of NEW calculators to generate (basic math + finance extras).
 * Existing keys are intentionally omitted.
 */

require_once __DIR__.'/catalog_helpers.php';

$catalog = [];

// ─── Basic Math ───────────────────────────────────────────────
$catalog[] = item('fraction_calculator', 'FractionCalculator', 'basic-math', 'Fraction Calculator', schema([
    num('numerator1', 'Numerator 1', ['default' => 1, 'step' => 1]),
    num('denominator1', 'Denominator 1', ['default' => 2, 'min' => 0.0000001, 'step' => 1]),
    sel('operation', 'Operation', ['add' => 'Add', 'subtract' => 'Subtract', 'multiply' => 'Multiply', 'divide' => 'Divide'], 'add'),
    num('numerator2', 'Numerator 2', ['default' => 1, 'step' => 1]),
    num('denominator2', 'Denominator 2', ['default' => 3, 'min' => 0.0000001, 'step' => 1]),
]), <<<'CODE'
        $n1 = $this->requireNumeric($inputs, 'numerator1');
        $d1 = $this->requireNumeric($inputs, 'denominator1');
        $n2 = $this->requireNumeric($inputs, 'numerator2');
        $d2 = $this->requireNumeric($inputs, 'denominator2');
        $op = $this->toString($inputs, 'operation', 'add');

        if ($d1 == 0.0 || $d2 == 0.0) {
            throw new InvalidArgumentException('Denominator cannot be zero.');
        }

        $a = $n1 / $d1;
        $b = $n2 / $d2;
        $result = match ($op) {
            'subtract' => $a - $b,
            'multiply' => $a * $b,
            'divide' => $this->safeDivide($a, $b),
            default => $a + $b,
        };

        return [
            'results' => ['decimal_result' => $this->round($result, 6)],
            'breakdown' => ['fraction_1' => $n1.'/'.$d1, 'fraction_2' => $n2.'/'.$d2, 'operation' => $op],
            'units' => ['decimal_result' => 'number'],
        ];
CODE);

$catalog[] = item('decimal_calculator', 'DecimalCalculator', 'basic-math', 'Decimal Calculator', schema([
    num('value', 'Decimal Value', ['default' => 0.75, 'step' => 0.0001, 'min' => -1e9]),
    num('places', 'Round To Places', ['default' => 2, 'min' => 0, 'max' => 10, 'step' => 1]),
]), <<<'CODE'
        $value = $this->requireNumeric($inputs, 'value');
        $places = (int) $this->requireNumeric($inputs, 'places');
        $rounded = round($value, $places);
        $percent = $value * 100;

        return [
            'results' => [
                'rounded' => $this->round($rounded, $places),
                'as_percent' => $this->round($percent, 4),
                'as_fraction_approx' => $this->round($value, 6),
            ],
            'breakdown' => ['original' => $value, 'places' => $places],
            'units' => ['rounded' => 'number', 'as_percent' => '%', 'as_fraction_approx' => 'decimal'],
        ];
CODE);

$catalog[] = item('ratio_calculator', 'RatioCalculator', 'basic-math', 'Ratio Calculator', schema([
    num('a', 'Part A', ['default' => 2]),
    num('b', 'Part B', ['default' => 3]),
    num('total', 'Scale To Total (optional)', ['default' => 100, 'required' => false]),
]), <<<'CODE'
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $total = $this->toFloat($inputs, 'total', 0);
        $sum = $a + $b;
        if ($sum == 0.0) {
            throw new InvalidArgumentException('Parts cannot both be zero.');
        }
        $ratio = $this->safeDivide($a, $b);
        $results = [
            'ratio_a_to_b' => $this->round($ratio, 6),
            'a_percent' => $this->round($this->percentageOf($a, $sum), 2),
            'b_percent' => $this->round($this->percentageOf($b, $sum), 2),
        ];
        if ($total > 0) {
            $results['scaled_a'] = $this->round($total * ($a / $sum), 2);
            $results['scaled_b'] = $this->round($total * ($b / $sum), 2);
        }
        return [
            'results' => $results,
            'breakdown' => ['part_a' => $a, 'part_b' => $b, 'sum' => $sum],
            'units' => ['ratio_a_to_b' => 'ratio', 'a_percent' => '%', 'b_percent' => '%', 'scaled_a' => 'number', 'scaled_b' => 'number'],
        ];
CODE);

$catalog[] = item('proportion_calculator', 'ProportionCalculator', 'basic-math', 'Proportion Calculator', schema([
    num('a', 'A', ['default' => 2]),
    num('b', 'B', ['default' => 4]),
    num('c', 'C', ['default' => 3]),
]), <<<'CODE'
        // A:B = C:D  => D = (B * C) / A
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $c = $this->requireNumeric($inputs, 'c');
        $d = $this->safeDivide($b * $c, $a);
        return [
            'results' => ['d' => $this->round($d, 6)],
            'breakdown' => ['proportion' => "{$a}:{$b} = {$c}:".$this->round($d, 6)],
            'units' => ['d' => 'number'],
        ];
CODE);

$catalog[] = item('average_calculator', 'AverageCalculator', 'basic-math', 'Average / Mean Calculator', schema([
    "            \$this->field('values', 'Values (comma-separated)', 'text', ['default' => '10, 20, 30, 40']),",
]), <<<'CODE'
        $raw = $this->toString($inputs, 'values', '');
        $parts = array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '');
        if (count($parts) === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $nums = array_map('floatval', $parts);
        $count = count($nums);
        $sum = array_sum($nums);
        $avg = $sum / $count;
        return [
            'results' => ['average' => $this->round($avg, 6), 'sum' => $this->round($sum, 6), 'count' => $count],
            'breakdown' => ['values' => implode(', ', $nums)],
            'units' => ['average' => 'number', 'sum' => 'number', 'count' => 'count'],
        ];
CODE);

$catalog[] = item('median_calculator', 'MedianCalculator', 'basic-math', 'Median Calculator', schema([
    "            \$this->field('values', 'Values (comma-separated)', 'text', ['default' => '3, 1, 4, 2, 5']),",
]), <<<'CODE'
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_values(array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '')));
        sort($nums);
        $n = count($nums);
        if ($n === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $median = $n % 2 === 1 ? $nums[intdiv($n, 2)] : (($nums[$n / 2 - 1] + $nums[$n / 2]) / 2);
        return [
            'results' => ['median' => $this->round($median, 6)],
            'breakdown' => ['sorted_values' => implode(', ', $nums), 'count' => $n],
            'units' => ['median' => 'number'],
        ];
CODE);

$catalog[] = item('mode_calculator', 'ModeCalculator', 'basic-math', 'Mode Calculator', schema([
    "            \$this->field('values', 'Values (comma-separated)', 'text', ['default' => '1, 2, 2, 3, 3, 3, 4']),",
]), <<<'CODE'
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_map('strval', array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== '')));
        if (count($nums) === 0) {
            throw new InvalidArgumentException('Enter at least one number.');
        }
        $freq = array_count_values($nums);
        $max = max($freq);
        $modes = array_keys(array_filter($freq, fn ($f) => $f === $max));
        return [
            'results' => ['mode' => implode(', ', $modes), 'frequency' => $max],
            'breakdown' => ['unique_values' => count($freq)],
            'units' => ['mode' => 'value(s)', 'frequency' => 'count'],
        ];
CODE);

$catalog[] = item('standard_deviation_calculator', 'StandardDeviationCalculator', 'basic-math', 'Standard Deviation Calculator', schema([
    "            \$this->field('values', 'Values (comma-separated)', 'text', ['default' => '10, 12, 23, 23, 16, 23, 21, 16']),",
    sel('type', 'Type', ['sample' => 'Sample (n-1)', 'population' => 'Population (n)'], 'sample'),
]), <<<'CODE'
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));
        $n = count($nums);
        if ($n < 2) {
            throw new InvalidArgumentException('Enter at least two numbers.');
        }
        $mean = array_sum($nums) / $n;
        $varianceSum = 0.0;
        foreach ($nums as $v) {
            $varianceSum += ($v - $mean) ** 2;
        }
        $divisor = $this->toString($inputs, 'type', 'sample') === 'population' ? $n : ($n - 1);
        $variance = $varianceSum / $divisor;
        $sd = sqrt($variance);
        return [
            'results' => ['standard_deviation' => $this->round($sd, 6), 'variance' => $this->round($variance, 6), 'mean' => $this->round($mean, 6)],
            'breakdown' => ['count' => $n, 'type' => $this->toString($inputs, 'type', 'sample')],
            'units' => ['standard_deviation' => 'number', 'variance' => 'number', 'mean' => 'number'],
        ];
CODE);

$catalog[] = item('variance_calculator', 'VarianceCalculator', 'basic-math', 'Variance Calculator', schema([
    "            \$this->field('values', 'Values (comma-separated)', 'text', ['default' => '4, 8, 6, 5, 3, 7']),",
    sel('type', 'Type', ['sample' => 'Sample (n-1)', 'population' => 'Population (n)'], 'sample'),
]), <<<'CODE'
        $raw = $this->toString($inputs, 'values', '');
        $nums = array_map('floatval', array_filter(array_map('trim', explode(',', $raw)), fn ($v) => $v !== ''));
        $n = count($nums);
        if ($n < 2) {
            throw new InvalidArgumentException('Enter at least two numbers.');
        }
        $mean = array_sum($nums) / $n;
        $varianceSum = 0.0;
        foreach ($nums as $v) {
            $varianceSum += ($v - $mean) ** 2;
        }
        $divisor = $this->toString($inputs, 'type', 'sample') === 'population' ? $n : ($n - 1);
        $variance = $varianceSum / $divisor;
        return [
            'results' => ['variance' => $this->round($variance, 6), 'mean' => $this->round($mean, 6)],
            'breakdown' => ['count' => $n],
            'units' => ['variance' => 'number', 'mean' => 'number'],
        ];
CODE);

$catalog[] = item('probability_calculator', 'ProbabilityCalculator', 'basic-math', 'Probability Calculator', schema([
    num('favorable', 'Favorable Outcomes', ['default' => 1, 'min' => 0, 'step' => 1]),
    num('total', 'Total Outcomes', ['default' => 6, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $favorable = $this->requireNumeric($inputs, 'favorable');
        $total = $this->requireNumeric($inputs, 'total');
        if ($favorable > $total) {
            throw new InvalidArgumentException('Favorable outcomes cannot exceed total outcomes.');
        }
        $p = $this->safeDivide($favorable, $total);
        return [
            'results' => [
                'probability' => $this->round($p, 6),
                'probability_percent' => $this->round($p * 100, 4),
                'odds_for' => $favorable.':'.($total - $favorable),
            ],
            'breakdown' => ['favorable' => $favorable, 'total' => $total],
            'units' => ['probability' => '0-1', 'probability_percent' => '%', 'odds_for' => 'ratio'],
        ];
CODE);

$catalog[] = item('quadratic_calculator', 'QuadraticCalculator', 'basic-math', 'Quadratic Equation Calculator', schema([
    num('a', 'Coefficient a', ['default' => 1, 'min' => -1e6]),
    num('b', 'Coefficient b', ['default' => -5, 'min' => -1e6]),
    num('c', 'Coefficient c', ['default' => 6, 'min' => -1e6]),
]), <<<'CODE'
        $a = $this->requireNumeric($inputs, 'a');
        $b = $this->requireNumeric($inputs, 'b');
        $c = $this->requireNumeric($inputs, 'c');
        if ($a == 0.0) {
            throw new InvalidArgumentException('Coefficient a cannot be zero for a quadratic equation.');
        }
        $disc = ($b ** 2) - (4 * $a * $c);
        $results = ['discriminant' => $this->round($disc, 6)];
        if ($disc > 0) {
            $results['root_1'] = $this->round((-$b + sqrt($disc)) / (2 * $a), 6);
            $results['root_2'] = $this->round((-$b - sqrt($disc)) / (2 * $a), 6);
        } elseif ($disc == 0.0) {
            $results['root'] = $this->round(-$b / (2 * $a), 6);
        } else {
            $real = -$b / (2 * $a);
            $imag = sqrt(abs($disc)) / (2 * $a);
            $results['root_1'] = $this->round($real, 6).' + '.$this->round($imag, 6).'i';
            $results['root_2'] = $this->round($real, 6).' - '.$this->round($imag, 6).'i';
        }
        return [
            'results' => $results,
            'breakdown' => ['equation' => "{$a}x² + {$b}x + {$c} = 0"],
            'units' => ['discriminant' => 'number', 'root_1' => 'x', 'root_2' => 'x', 'root' => 'x'],
        ];
CODE);

$catalog[] = item('log_calculator', 'LogCalculator', 'basic-math', 'Logarithm Calculator', schema([
    num('value', 'Value', ['default' => 100, 'min' => 0.0000000001]),
    num('base', 'Base', ['default' => 10, 'min' => 0.0000000001]),
]), <<<'CODE'
        $value = $this->requireNumeric($inputs, 'value');
        $base = $this->requireNumeric($inputs, 'base');
        if ($value <= 0 || $base <= 0 || $base == 1.0) {
            throw new InvalidArgumentException('Value must be > 0 and base must be > 0 and ≠ 1.');
        }
        $result = log($value) / log($base);
        return [
            'results' => ['logarithm' => $this->round($result, 8), 'natural_log' => $this->round(log($value), 8)],
            'breakdown' => ['value' => $value, 'base' => $base],
            'units' => ['logarithm' => 'number', 'natural_log' => 'ln'],
        ];
CODE);

$catalog[] = item('exponent_calculator', 'ExponentCalculator', 'basic-math', 'Exponent Calculator', schema([
    num('base', 'Base', ['default' => 2, 'min' => -1e6]),
    num('exponent', 'Exponent', ['default' => 8, 'min' => -100, 'max' => 100]),
]), <<<'CODE'
        $base = $this->requireNumeric($inputs, 'base');
        $exp = $this->requireNumeric($inputs, 'exponent');
        $result = $base ** $exp;
        return [
            'results' => ['result' => $this->round($result, 8)],
            'breakdown' => ['expression' => "{$base}^{$exp}"],
            'units' => ['result' => 'number'],
        ];
CODE);

$catalog[] = item('factorial_calculator', 'FactorialCalculator', 'basic-math', 'Factorial Calculator', schema([
    num('n', 'n', ['default' => 5, 'min' => 0, 'max' => 170, 'step' => 1]),
]), <<<'CODE'
        $n = (int) $this->requireNumeric($inputs, 'n');
        if ($n < 0) {
            throw new InvalidArgumentException('n must be non-negative.');
        }
        $result = 1.0;
        for ($i = 2; $i <= $n; $i++) {
            $result *= $i;
        }
        return [
            'results' => ['factorial' => $result],
            'breakdown' => ['expression' => $n.'!'],
            'units' => ['factorial' => 'number'],
        ];
CODE);

$catalog[] = item('prime_number_calculator', 'PrimeNumberCalculator', 'basic-math', 'Prime Number Checker', schema([
    num('number', 'Number', ['default' => 17, 'min' => 0, 'max' => 10000000, 'step' => 1]),
]), <<<'CODE'
        $number = (int) $this->requireNumeric($inputs, 'number');
        $isPrime = $number > 1;
        if ($isPrime) {
            for ($i = 2; $i * $i <= $number; $i++) {
                if ($number % $i === 0) {
                    $isPrime = false;
                    break;
                }
            }
        }
        return [
            'results' => ['is_prime' => $isPrime ? 'Yes' : 'No', 'number' => $number],
            'breakdown' => ['checked_up_to' => (int) sqrt(max($number, 0))],
            'units' => ['is_prime' => 'boolean', 'number' => 'integer'],
        ];
CODE);

$catalog[] = item('gcd_calculator', 'GcdCalculator', 'basic-math', 'GCD Calculator', schema([
    num('a', 'Number A', ['default' => 48, 'step' => 1, 'min' => 0]),
    num('b', 'Number B', ['default' => 18, 'step' => 1, 'min' => 0]),
]), <<<'CODE'
        $a = abs((int) $this->requireNumeric($inputs, 'a'));
        $b = abs((int) $this->requireNumeric($inputs, 'b'));
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        return [
            'results' => ['gcd' => $a],
            'breakdown' => ['method' => 'Euclidean algorithm'],
            'units' => ['gcd' => 'integer'],
        ];
CODE);

$catalog[] = item('lcm_calculator', 'LcmCalculator', 'basic-math', 'LCM Calculator', schema([
    num('a', 'Number A', ['default' => 12, 'step' => 1, 'min' => 1]),
    num('b', 'Number B', ['default' => 18, 'step' => 1, 'min' => 1]),
]), <<<'CODE'
        $x = abs((int) $this->requireNumeric($inputs, 'a'));
        $y = abs((int) $this->requireNumeric($inputs, 'b'));
        $a = $x; $b = $y;
        while ($b !== 0) {
            [$a, $b] = [$b, $a % $b];
        }
        $gcd = $a;
        $lcm = (int) (($x / $gcd) * $y);
        return [
            'results' => ['lcm' => $lcm, 'gcd' => $gcd],
            'breakdown' => ['formula' => 'LCM = |a·b| / GCD'],
            'units' => ['lcm' => 'integer', 'gcd' => 'integer'],
        ];
CODE);

$catalog[] = item('random_number_generator', 'RandomNumberGenerator', 'basic-math', 'Random Number Generator', schema([
    num('min', 'Minimum', ['default' => 1, 'step' => 1, 'min' => -1e9]),
    num('max', 'Maximum', ['default' => 100, 'step' => 1, 'min' => -1e9]),
    num('count', 'How Many', ['default' => 1, 'min' => 1, 'max' => 50, 'step' => 1]),
]), <<<'CODE'
        $min = (int) $this->requireNumeric($inputs, 'min');
        $max = (int) $this->requireNumeric($inputs, 'max');
        $count = (int) $this->requireNumeric($inputs, 'count');
        if ($min > $max) {
            throw new InvalidArgumentException('Minimum cannot be greater than maximum.');
        }
        $numbers = [];
        for ($i = 0; $i < $count; $i++) {
            $numbers[] = random_int($min, $max);
        }
        return [
            'results' => ['random_numbers' => implode(', ', $numbers)],
            'breakdown' => ['range' => "{$min} to {$max}", 'count' => $count],
            'units' => ['random_numbers' => 'integers'],
        ];
CODE);

// ─── Finance extras ───────────────────────────────────────────
$catalog[] = item('simple_interest_calculator', 'SimpleInterestCalculator', 'finance', 'Simple Interest Calculator', schema([
    num('principal', 'Principal', ['default' => 100000, 'unit' => 'currency']),
    num('rate', 'Annual Rate', ['default' => 8, 'unit' => '%', 'max' => 100]),
    num('time_years', 'Time', ['default' => 2, 'unit' => 'years', 'min' => 0.01]),
]), <<<'CODE'
        $p = $this->requireNumeric($inputs, 'principal');
        $r = $this->requireNumeric($inputs, 'rate');
        $t = $this->requireNumeric($inputs, 'time_years');
        $interest = $p * $r * $t / 100;
        return [
            'results' => ['interest' => $this->round($interest), 'total_amount' => $this->round($p + $interest)],
            'breakdown' => ['formula' => 'I = P × R × T / 100'],
            'units' => ['interest' => 'currency', 'total_amount' => 'currency'],
        ];
CODE);

$catalog[] = item('mutual_fund_calculator', 'MutualFundCalculator', 'finance', 'Mutual Fund Calculator', schema([
    num('monthly_investment', 'Monthly Investment', ['default' => 5000, 'unit' => 'currency']),
    num('expected_return', 'Expected Annual Return', ['default' => 12, 'unit' => '%', 'max' => 50]),
    num('years', 'Years', ['default' => 10, 'min' => 0.5, 'max' => 50]),
]), <<<'CODE'
        $sip = $this->requireNumeric($inputs, 'monthly_investment');
        $annual = $this->requireNumeric($inputs, 'expected_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        $fv = $r == 0.0 ? $sip * $months : $sip * (((1 + $r) ** $months - 1) / $r) * (1 + $r);
        $invested = $sip * $months;
        return [
            'results' => [
                'future_value' => $this->round($fv),
                'total_invested' => $this->round($invested),
                'estimated_gain' => $this->round($fv - $invested),
            ],
            'breakdown' => ['months' => $months, 'monthly_rate_percent' => $this->round($r * 100, 4)],
            'units' => ['future_value' => 'currency', 'total_invested' => 'currency', 'estimated_gain' => 'currency'],
        ];
CODE);

$catalog[] = item('fd_calculator', 'FdCalculator', 'finance', 'FD Calculator', schema([
    num('principal', 'Deposit Amount', ['default' => 100000, 'unit' => 'currency']),
    num('rate', 'Annual Interest Rate', ['default' => 7.5, 'unit' => '%', 'max' => 50]),
    num('years', 'Tenure (Years)', ['default' => 3, 'min' => 0.25, 'max' => 20]),
    sel('compounding', 'Compounding', ['yearly' => 'Yearly', 'half_yearly' => 'Half-Yearly', 'quarterly' => 'Quarterly', 'monthly' => 'Monthly'], 'quarterly'),
]), <<<'CODE'
        $p = $this->requireNumeric($inputs, 'principal');
        $rate = $this->requireNumeric($inputs, 'rate');
        $years = $this->requireNumeric($inputs, 'years');
        $n = match ($this->toString($inputs, 'compounding', 'quarterly')) {
            'yearly' => 1, 'half_yearly' => 2, 'monthly' => 12, default => 4,
        };
        $amount = $p * ((1 + ($rate / 100) / $n) ** ($n * $years));
        return [
            'results' => ['maturity_amount' => $this->round($amount), 'interest_earned' => $this->round($amount - $p)],
            'breakdown' => ['compounding_per_year' => $n],
            'units' => ['maturity_amount' => 'currency', 'interest_earned' => 'currency'],
        ];
CODE);

$catalog[] = item('rd_calculator', 'RdCalculator', 'finance', 'RD Calculator', schema([
    num('monthly_deposit', 'Monthly Deposit', ['default' => 5000, 'unit' => 'currency']),
    num('rate', 'Annual Interest Rate', ['default' => 6.5, 'unit' => '%', 'max' => 50]),
    num('months', 'Tenure (Months)', ['default' => 24, 'min' => 1, 'max' => 120, 'step' => 1]),
]), <<<'CODE'
        $p = $this->requireNumeric($inputs, 'monthly_deposit');
        $rate = $this->requireNumeric($inputs, 'rate');
        $n = (int) $this->requireNumeric($inputs, 'months');
        $r = $rate / 400; // quarterly compounding approximation used by many banks
        $maturity = $p * (((1 + $r) ** ($n / 3) - 1) / (1 - (1 + $r) ** (-1 / 3)));
        $invested = $p * $n;
        return [
            'results' => [
                'maturity_amount' => $this->round($maturity),
                'total_deposited' => $this->round($invested),
                'interest_earned' => $this->round($maturity - $invested),
            ],
            'breakdown' => ['months' => $n],
            'units' => ['maturity_amount' => 'currency', 'total_deposited' => 'currency', 'interest_earned' => 'currency'],
        ];
CODE);

$catalog[] = item('retirement_calculator', 'RetirementCalculator', 'finance', 'Retirement Calculator', schema([
    num('current_savings', 'Current Savings', ['default' => 500000, 'unit' => 'currency', 'min' => 0]),
    num('monthly_contribution', 'Monthly Contribution', ['default' => 10000, 'unit' => 'currency']),
    num('annual_return', 'Expected Annual Return', ['default' => 10, 'unit' => '%', 'max' => 30]),
    num('years', 'Years Until Retirement', ['default' => 25, 'min' => 1, 'max' => 50]),
]), <<<'CODE'
        $pv = $this->requireNumeric($inputs, 'current_savings');
        $pmt = $this->requireNumeric($inputs, 'monthly_contribution');
        $annual = $this->requireNumeric($inputs, 'annual_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        $fvLump = $pv * ((1 + $r) ** $months);
        $fvSip = $r == 0.0 ? $pmt * $months : $pmt * (((1 + $r) ** $months - 1) / $r) * (1 + $r);
        $total = $fvLump + $fvSip;
        return [
            'results' => [
                'retirement_corpus' => $this->round($total),
                'from_current_savings' => $this->round($fvLump),
                'from_contributions' => $this->round($fvSip),
            ],
            'breakdown' => ['months' => $months],
            'units' => ['retirement_corpus' => 'currency', 'from_current_savings' => 'currency', 'from_contributions' => 'currency'],
        ];
CODE);

$catalog[] = item('pension_calculator', 'PensionCalculator', 'finance', 'Pension Calculator', schema([
    num('corpus', 'Retirement Corpus', ['default' => 5000000, 'unit' => 'currency']),
    num('annual_return', 'Expected Annual Return', ['default' => 6, 'unit' => '%', 'max' => 20]),
    num('years', 'Pension Years', ['default' => 20, 'min' => 1, 'max' => 40]),
]), <<<'CODE'
        $corpus = $this->requireNumeric($inputs, 'corpus');
        $annual = $this->requireNumeric($inputs, 'annual_return');
        $years = $this->requireNumeric($inputs, 'years');
        $months = (int) round($years * 12);
        $r = $annual / 12 / 100;
        // PMT for annuity
        $monthly = $r == 0.0
            ? $this->safeDivide($corpus, $months)
            : $corpus * $r / (1 - (1 + $r) ** (-$months));
        return [
            'results' => [
                'monthly_pension' => $this->round($monthly),
                'annual_pension' => $this->round($monthly * 12),
                'total_payout' => $this->round($monthly * $months),
            ],
            'breakdown' => ['months' => $months],
            'units' => ['monthly_pension' => 'currency', 'annual_pension' => 'currency', 'total_payout' => 'currency'],
        ];
CODE);

$catalog[] = item('salary_calculator', 'SalaryCalculator', 'finance', 'Salary Calculator', schema([
    num('gross_monthly', 'Gross Monthly Salary', ['default' => 50000, 'unit' => 'currency']),
    num('deductions', 'Monthly Deductions', ['default' => 5000, 'unit' => 'currency', 'min' => 0]),
    num('bonus_months', 'Bonus Months / Year', ['default' => 1, 'min' => 0, 'max' => 12, 'step' => 0.5]),
]), <<<'CODE'
        $gross = $this->requireNumeric($inputs, 'gross_monthly');
        $ded = $this->requireNumeric($inputs, 'deductions');
        $bonus = $this->requireNumeric($inputs, 'bonus_months');
        $net = $gross - $ded;
        $annualCtc = $gross * (12 + $bonus);
        return [
            'results' => [
                'net_monthly' => $this->round($net),
                'annual_ctc' => $this->round($annualCtc),
                'annual_net_approx' => $this->round($net * 12),
            ],
            'breakdown' => ['gross_monthly' => $gross, 'deductions' => $ded],
            'units' => ['net_monthly' => 'currency', 'annual_ctc' => 'currency', 'annual_net_approx' => 'currency'],
        ];
CODE);

$catalog[] = item('tds_calculator', 'TdsCalculator', 'finance', 'TDS Calculator', schema([
    num('amount', 'Payment Amount', ['default' => 100000, 'unit' => 'currency']),
    num('tds_rate', 'TDS Rate', ['default' => 10, 'unit' => '%', 'max' => 40]),
]), <<<'CODE'
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'tds_rate');
        $tds = $amount * $rate / 100;
        return [
            'results' => [
                'tds_amount' => $this->round($tds),
                'net_payable' => $this->round($amount - $tds),
            ],
            'breakdown' => ['gross_amount' => $amount, 'rate_percent' => $rate],
            'units' => ['tds_amount' => 'currency', 'net_payable' => 'currency'],
        ];
CODE);

$catalog[] = item('break_even_calculator', 'BreakEvenCalculator', 'finance', 'Break-even Calculator', schema([
    num('fixed_costs', 'Fixed Costs', ['default' => 100000, 'unit' => 'currency']),
    num('price_per_unit', 'Price Per Unit', ['default' => 50, 'unit' => 'currency', 'min' => 0.01]),
    num('variable_cost_per_unit', 'Variable Cost Per Unit', ['default' => 30, 'unit' => 'currency', 'min' => 0]),
]), <<<'CODE'
        $fixed = $this->requireNumeric($inputs, 'fixed_costs');
        $price = $this->requireNumeric($inputs, 'price_per_unit');
        $variable = $this->requireNumeric($inputs, 'variable_cost_per_unit');
        $contribution = $price - $variable;
        if ($contribution <= 0) {
            throw new InvalidArgumentException('Price must be greater than variable cost.');
        }
        $units = $fixed / $contribution;
        return [
            'results' => [
                'break_even_units' => $this->round($units, 2),
                'break_even_revenue' => $this->round($units * $price),
                'contribution_margin' => $this->round($contribution),
            ],
            'breakdown' => ['fixed_costs' => $fixed],
            'units' => ['break_even_units' => 'units', 'break_even_revenue' => 'currency', 'contribution_margin' => 'currency'],
        ];
CODE);

$catalog[] = item('inflation_calculator', 'InflationCalculator', 'finance', 'Inflation Calculator', schema([
    num('present_value', 'Present Amount', ['default' => 100000, 'unit' => 'currency']),
    num('inflation_rate', 'Annual Inflation Rate', ['default' => 5, 'unit' => '%', 'max' => 50]),
    num('years', 'Years', ['default' => 10, 'min' => 1, 'max' => 50]),
]), <<<'CODE'
        $pv = $this->requireNumeric($inputs, 'present_value');
        $rate = $this->requireNumeric($inputs, 'inflation_rate');
        $years = $this->requireNumeric($inputs, 'years');
        $fv = $pv * ((1 + $rate / 100) ** $years);
        return [
            'results' => [
                'future_cost' => $this->round($fv),
                'purchasing_power_loss' => $this->round($fv - $pv),
                'today_value_of_future_money' => $this->round($pv / ((1 + $rate / 100) ** $years)),
            ],
            'breakdown' => ['years' => $years, 'inflation_rate' => $rate],
            'units' => ['future_cost' => 'currency', 'purchasing_power_loss' => 'currency', 'today_value_of_future_money' => 'currency'],
        ];
CODE);

return $catalog;
