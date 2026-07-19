<?php

require_once __DIR__.'/catalog_helpers.php';

$part5 = [];

// ─── Business ─────────────────────────────────────────────────
$part5[] = item('roe_calculator', 'RoeCalculator', 'business', 'ROE Calculator', schema([
    num('net_income', 'Net Income', ['default' => 250000, 'unit' => 'currency']),
    num('equity', 'Shareholder Equity', ['default' => 1000000, 'unit' => 'currency', 'min' => 0.01]),
]), <<<'CODE'
        $roe = $this->percentageOf($this->requireNumeric($inputs, 'net_income'), $this->requireNumeric($inputs, 'equity'));
        return [
            'results' => ['roe_percent' => $this->round($roe, 2)],
            'breakdown' => ['formula' => 'ROE = Net Income / Equity × 100'],
            'units' => ['roe_percent' => '%'],
        ];
CODE);

$part5[] = item('ebitda_calculator', 'EbitdaCalculator', 'business', 'EBITDA Calculator', schema([
    num('operating_income', 'Operating Income (EBIT)', ['default' => 500000, 'unit' => 'currency', 'min' => -1e12]),
    num('depreciation', 'Depreciation', ['default' => 50000, 'unit' => 'currency', 'min' => 0]),
    num('amortization', 'Amortization', ['default' => 20000, 'unit' => 'currency', 'min' => 0]),
]), <<<'CODE'
        $ebitda = $this->requireNumeric($inputs, 'operating_income')
            + $this->requireNumeric($inputs, 'depreciation')
            + $this->requireNumeric($inputs, 'amortization');
        return [
            'results' => ['ebitda' => $this->round($ebitda)],
            'breakdown' => ['formula' => 'EBIT + D&A'],
            'units' => ['ebitda' => 'currency'],
        ];
CODE);

$part5[] = item('invoice_calculator', 'InvoiceCalculator', 'business', 'Invoice Calculator', schema([
    num('subtotal', 'Subtotal', ['default' => 10000, 'unit' => 'currency']),
    num('tax_percent', 'Tax %', ['default' => 13, 'unit' => '%', 'max' => 40]),
    num('discount_percent', 'Discount %', ['default' => 0, 'unit' => '%', 'max' => 100, 'required' => false]),
]), <<<'CODE'
        $sub = $this->requireNumeric($inputs, 'subtotal');
        $discount = $sub * $this->toFloat($inputs, 'discount_percent') / 100;
        $taxable = $sub - $discount;
        $tax = $taxable * $this->requireNumeric($inputs, 'tax_percent') / 100;
        return [
            'results' => [
                'discount_amount' => $this->round($discount),
                'tax_amount' => $this->round($tax),
                'grand_total' => $this->round($taxable + $tax),
            ],
            'breakdown' => ['taxable_amount' => $this->round($taxable)],
            'units' => ['discount_amount' => 'currency', 'tax_amount' => 'currency', 'grand_total' => 'currency'],
        ];
CODE);

$part5[] = item('inventory_calculator', 'InventoryCalculator', 'business', 'Inventory Turnover Calculator', schema([
    num('cogs', 'Cost of Goods Sold', ['default' => 500000, 'unit' => 'currency']),
    num('avg_inventory', 'Average Inventory', ['default' => 100000, 'unit' => 'currency', 'min' => 0.01]),
]), <<<'CODE'
        $turnover = $this->safeDivide($this->requireNumeric($inputs, 'cogs'), $this->requireNumeric($inputs, 'avg_inventory'));
        $days = $this->safeDivide(365, $turnover);
        return [
            'results' => [
                'turnover_ratio' => $this->round($turnover, 2),
                'days_inventory_outstanding' => $this->round($days, 1),
            ],
            'breakdown' => ['formula' => 'COGS / Avg Inventory'],
            'units' => ['turnover_ratio' => '×', 'days_inventory_outstanding' => 'days'],
        ];
CODE);

$part5[] = item('sales_tax_calculator', 'SalesTaxCalculator', 'business', 'Sales Tax Calculator', schema([
    num('amount', 'Amount', ['default' => 1000, 'unit' => 'currency']),
    num('tax_rate', 'Tax Rate', ['default' => 13, 'unit' => '%', 'max' => 40]),
    sel('mode', 'Mode', ['exclusive' => 'Tax exclusive', 'inclusive' => 'Tax inclusive'], 'exclusive'),
]), <<<'CODE'
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'tax_rate');
        if ($this->toString($inputs, 'mode', 'exclusive') === 'inclusive') {
            $base = $amount / (1 + $rate / 100);
            $tax = $amount - $base;
            $total = $amount;
        } else {
            $base = $amount;
            $tax = $amount * $rate / 100;
            $total = $amount + $tax;
        }
        return [
            'results' => [
                'taxable_base' => $this->round($base),
                'tax_amount' => $this->round($tax),
                'total' => $this->round($total),
            ],
            'breakdown' => ['rate_percent' => $rate],
            'units' => ['taxable_base' => 'currency', 'tax_amount' => 'currency', 'total' => 'currency'],
        ];
CODE);

$part5[] = item('password_strength_calculator', 'PasswordStrengthCalculator', 'internet-it', 'Password Strength Checker', schema([
    "            \$this->field('password', 'Password', 'text', ['default' => 'MyP@ssw0rd']),",
]), <<<'CODE'
        $password = $this->toString($inputs, 'password', '');
        $score = 0;
        $len = strlen($password);
        if ($len >= 8) { $score += 25; }
        if ($len >= 12) { $score += 15; }
        if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) { $score += 20; }
        if (preg_match('/\d/', $password)) { $score += 20; }
        if (preg_match('/[^A-Za-z0-9]/', $password)) { $score += 20; }
        $label = match (true) {
            $score >= 80 => 'Strong',
            $score >= 60 => 'Good',
            $score >= 40 => 'Fair',
            default => 'Weak',
        };
        return [
            'results' => ['strength_score' => $score, 'strength_label' => $label, 'length' => $len],
            'breakdown' => ['note' => 'Heuristic score only — not a security audit'],
            'units' => ['strength_score' => '0-100', 'strength_label' => 'rating', 'length' => 'chars'],
        ];
CODE);

$part5[] = item('openai_token_calculator', 'OpenaiTokenCalculator', 'developer', 'OpenAI Token Cost Calculator', schema([
    num('input_tokens', 'Input Tokens', ['default' => 1000, 'min' => 0, 'step' => 1]),
    num('output_tokens', 'Output Tokens', ['default' => 500, 'min' => 0, 'step' => 1]),
    num('input_price', 'Input Price / 1M tokens', ['default' => 0.15, 'unit' => 'USD', 'min' => 0]),
    num('output_price', 'Output Price / 1M tokens', ['default' => 0.60, 'unit' => 'USD', 'min' => 0]),
]), <<<'CODE'
        $in = $this->requireNumeric($inputs, 'input_tokens');
        $out = $this->requireNumeric($inputs, 'output_tokens');
        $inCost = ($in / 1_000_000) * $this->requireNumeric($inputs, 'input_price');
        $outCost = ($out / 1_000_000) * $this->requireNumeric($inputs, 'output_price');
        return [
            'results' => [
                'input_cost' => $this->round($inCost, 6),
                'output_cost' => $this->round($outCost, 6),
                'total_cost' => $this->round($inCost + $outCost, 6),
                'total_tokens' => (int) ($in + $out),
            ],
            'breakdown' => ['note' => 'Enter your model’s published per-1M rates'],
            'units' => ['input_cost' => 'USD', 'output_cost' => 'USD', 'total_cost' => 'USD', 'total_tokens' => 'tokens'],
        ];
CODE);

$part5[] = item('api_cost_calculator', 'ApiCostCalculator', 'developer', 'API Cost Calculator', schema([
    num('requests', 'Monthly Requests', ['default' => 100000, 'min' => 0, 'step' => 1]),
    num('price_per_1k', 'Price Per 1,000 Requests', ['default' => 0.5, 'unit' => 'USD', 'min' => 0]),
    num('free_tier', 'Free Tier Requests', ['default' => 10000, 'min' => 0, 'step' => 1, 'required' => false]),
]), <<<'CODE'
        $requests = $this->requireNumeric($inputs, 'requests');
        $free = $this->toFloat($inputs, 'free_tier');
        $billable = max(0, $requests - $free);
        $cost = ($billable / 1000) * $this->requireNumeric($inputs, 'price_per_1k');
        return [
            'results' => [
                'billable_requests' => (int) $billable,
                'estimated_monthly_cost' => $this->round($cost, 4),
            ],
            'breakdown' => ['free_tier' => $free],
            'units' => ['billable_requests' => 'requests', 'estimated_monthly_cost' => 'USD'],
        ];
CODE);

// ─── Agriculture ──────────────────────────────────────────────
$part5[] = item('seed_calculator', 'SeedCalculator', 'agriculture', 'Seed Calculator', schema([
    num('area', 'Area', ['default' => 1, 'unit' => 'hectare', 'min' => 0.01]),
    num('seed_rate', 'Seed Rate', ['default' => 80, 'unit' => 'kg/ha']),
]), <<<'CODE'
        $seed = $this->requireNumeric($inputs, 'area') * $this->requireNumeric($inputs, 'seed_rate');
        return [
            'results' => ['seed_required_kg' => $this->round($seed, 2)],
            'breakdown' => ['formula' => 'area × seed rate'],
            'units' => ['seed_required_kg' => 'kg'],
        ];
CODE);

$part5[] = item('fertilizer_calculator', 'FertilizerCalculator', 'agriculture', 'Fertilizer Calculator', schema([
    num('area', 'Area', ['default' => 1, 'unit' => 'hectare']),
    num('n_rate', 'Nitrogen Rate', ['default' => 100, 'unit' => 'kg/ha']),
    num('p_rate', 'Phosphorus Rate', ['default' => 50, 'unit' => 'kg/ha']),
    num('k_rate', 'Potassium Rate', ['default' => 40, 'unit' => 'kg/ha']),
]), <<<'CODE'
        $area = $this->requireNumeric($inputs, 'area');
        return [
            'results' => [
                'nitrogen_kg' => $this->round($area * $this->requireNumeric($inputs, 'n_rate'), 1),
                'phosphorus_kg' => $this->round($area * $this->requireNumeric($inputs, 'p_rate'), 1),
                'potassium_kg' => $this->round($area * $this->requireNumeric($inputs, 'k_rate'), 1),
            ],
            'breakdown' => ['area_ha' => $area],
            'units' => ['nitrogen_kg' => 'kg', 'phosphorus_kg' => 'kg', 'potassium_kg' => 'kg'],
        ];
CODE);

$part5[] = item('irrigation_calculator', 'IrrigationCalculator', 'agriculture', 'Irrigation Water Calculator', schema([
    num('area_m2', 'Area', ['default' => 1000, 'unit' => 'm²']),
    num('depth_mm', 'Irrigation Depth', ['default' => 25, 'unit' => 'mm', 'min' => 1]),
]), <<<'CODE'
        // 1 mm over 1 m² = 1 liter
        $liters = $this->requireNumeric($inputs, 'area_m2') * $this->requireNumeric($inputs, 'depth_mm');
        return [
            'results' => [
                'water_liters' => $this->round($liters),
                'water_m3' => $this->round($liters / 1000, 3),
            ],
            'breakdown' => ['rule' => '1 mm × 1 m² = 1 L'],
            'units' => ['water_liters' => 'L', 'water_m3' => 'm³'],
        ];
CODE);

$part5[] = item('livestock_feed_calculator', 'LivestockFeedCalculator', 'agriculture', 'Livestock Feed Calculator', schema([
    num('animals', 'Number of Animals', ['default' => 10, 'min' => 1, 'step' => 1]),
    num('feed_per_day', 'Feed Per Animal / Day', ['default' => 2.5, 'unit' => 'kg']),
    num('days', 'Days', ['default' => 30, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $total = $this->requireNumeric($inputs, 'animals') * $this->requireNumeric($inputs, 'feed_per_day') * $this->requireNumeric($inputs, 'days');
        return [
            'results' => ['total_feed_kg' => $this->round($total, 1), 'total_feed_tons' => $this->round($total / 1000, 3)],
            'breakdown' => [],
            'units' => ['total_feed_kg' => 'kg', 'total_feed_tons' => 'tons'],
        ];
CODE);

$part5[] = item('crop_yield_calculator', 'CropYieldCalculator', 'agriculture', 'Crop Yield Calculator', schema([
    num('total_production', 'Total Production', ['default' => 5000, 'unit' => 'kg']),
    num('area', 'Area', ['default' => 2, 'unit' => 'hectare', 'min' => 0.01]),
]), <<<'CODE'
        $yield = $this->safeDivide($this->requireNumeric($inputs, 'total_production'), $this->requireNumeric($inputs, 'area'));
        return [
            'results' => ['yield_kg_per_ha' => $this->round($yield, 2), 'yield_tons_per_ha' => $this->round($yield / 1000, 3)],
            'breakdown' => [],
            'units' => ['yield_kg_per_ha' => 'kg/ha', 'yield_tons_per_ha' => 't/ha'],
        ];
CODE);

// ─── Real estate ──────────────────────────────────────────────
$part5[] = item('rent_calculator', 'RentCalculator', 'real-estate', 'Rent Affordability Calculator', schema([
    num('monthly_income', 'Monthly Income', ['default' => 80000, 'unit' => 'currency']),
    num('rent_ratio', 'Max Rent % of Income', ['default' => 30, 'unit' => '%', 'max' => 60]),
]), <<<'CODE'
        $income = $this->requireNumeric($inputs, 'monthly_income');
        $ratio = $this->requireNumeric($inputs, 'rent_ratio') / 100;
        $rent = $income * $ratio;
        return [
            'results' => [
                'max_monthly_rent' => $this->round($rent),
                'max_annual_rent' => $this->round($rent * 12),
            ],
            'breakdown' => ['rule' => 'Income × rent ratio'],
            'units' => ['max_monthly_rent' => 'currency', 'max_annual_rent' => 'currency'],
        ];
CODE);

$part5[] = item('property_tax_calculator', 'PropertyTaxCalculator', 'real-estate', 'Property Tax Calculator', schema([
    num('assessed_value', 'Assessed Value', ['default' => 5000000, 'unit' => 'currency']),
    num('tax_rate', 'Annual Tax Rate', ['default' => 0.5, 'unit' => '%', 'max' => 10, 'step' => 0.01]),
]), <<<'CODE'
        $tax = $this->requireNumeric($inputs, 'assessed_value') * $this->requireNumeric($inputs, 'tax_rate') / 100;
        return [
            'results' => ['annual_tax' => $this->round($tax), 'monthly_tax' => $this->round($tax / 12)],
            'breakdown' => [],
            'units' => ['annual_tax' => 'currency', 'monthly_tax' => 'currency'],
        ];
CODE);

$part5[] = item('rental_yield_calculator', 'RentalYieldCalculator', 'real-estate', 'Rental Yield Calculator', schema([
    num('annual_rent', 'Annual Rent', ['default' => 360000, 'unit' => 'currency']),
    num('property_value', 'Property Value', ['default' => 10000000, 'unit' => 'currency', 'min' => 1]),
    num('annual_expenses', 'Annual Expenses', ['default' => 40000, 'unit' => 'currency', 'min' => 0, 'required' => false]),
]), <<<'CODE'
        $rent = $this->requireNumeric($inputs, 'annual_rent');
        $value = $this->requireNumeric($inputs, 'property_value');
        $expenses = $this->toFloat($inputs, 'annual_expenses');
        $gross = $this->percentageOf($rent, $value);
        $net = $this->percentageOf($rent - $expenses, $value);
        return [
            'results' => [
                'gross_yield_percent' => $this->round($gross, 2),
                'net_yield_percent' => $this->round($net, 2),
            ],
            'breakdown' => ['net_income' => $this->round($rent - $expenses)],
            'units' => ['gross_yield_percent' => '%', 'net_yield_percent' => '%'],
        ];
CODE);

// ─── Nepal special ────────────────────────────────────────────
$part5[] = item('nepal_income_tax_calculator', 'NepalIncomeTaxCalculator', 'nepal', 'Nepal Income Tax Calculator', schema([
    num('annual_income', 'Taxable Annual Income', ['default' => 600000, 'unit' => 'NPR']),
    sel('status', 'Filing Status', ['individual' => 'Individual', 'couple' => 'Couple'], 'individual'),
]), <<<'CODE'
        // Simplified FY slabs (illustrative — update when IRD changes)
        $income = $this->requireNumeric($inputs, 'annual_income');
        $slabs = $this->toString($inputs, 'status', 'individual') === 'couple'
            ? [[600000, 0.01], [200000, 0.10], [300000, 0.20], [900000, 0.30], [PHP_FLOAT_MAX, 0.36]]
            : [[500000, 0.01], [200000, 0.10], [300000, 0.20], [1000000, 0.30], [PHP_FLOAT_MAX, 0.36]];
        $remaining = $income;
        $tax = 0.0;
        foreach ($slabs as [$width, $rate]) {
            $chunk = min($remaining, $width);
            $tax += $chunk * $rate;
            $remaining -= $chunk;
            if ($remaining <= 0) {
                break;
            }
        }
        return [
            'results' => [
                'estimated_tax' => $this->round($tax),
                'effective_rate_percent' => $this->round($this->percentageOf($tax, max($income, 1)), 2),
                'net_income' => $this->round($income - $tax),
            ],
            'breakdown' => ['note' => 'Simplified illustrative slabs — verify with latest IRD rules / tax advisor'],
            'units' => ['estimated_tax' => 'NPR', 'effective_rate_percent' => '%', 'net_income' => 'NPR'],
        ];
CODE);

$part5[] = item('nepal_vat_calculator', 'NepalVatCalculator', 'nepal', 'Nepal VAT Calculator', schema([
    num('amount', 'Amount', ['default' => 10000, 'unit' => 'NPR']),
    num('vat_rate', 'VAT Rate', ['default' => 13, 'unit' => '%', 'max' => 30]),
    sel('mode', 'Mode', ['exclusive' => 'Add VAT', 'inclusive' => 'Extract VAT'], 'exclusive'),
]), <<<'CODE'
        $amount = $this->requireNumeric($inputs, 'amount');
        $rate = $this->requireNumeric($inputs, 'vat_rate');
        if ($this->toString($inputs, 'mode', 'exclusive') === 'inclusive') {
            $base = $amount / (1 + $rate / 100);
            $vat = $amount - $base;
            $total = $amount;
        } else {
            $base = $amount;
            $vat = $amount * $rate / 100;
            $total = $amount + $vat;
        }
        return [
            'results' => [
                'taxable_amount' => $this->round($base),
                'vat_amount' => $this->round($vat),
                'total_amount' => $this->round($total),
            ],
            'breakdown' => ['standard_rate_note' => 'Nepal standard VAT is commonly 13%'],
            'units' => ['taxable_amount' => 'NPR', 'vat_amount' => 'NPR', 'total_amount' => 'NPR'],
        ];
CODE);

$part5[] = item('nepal_tds_calculator', 'NepalTdsCalculator', 'nepal', 'Nepal TDS Calculator', schema([
    num('payment', 'Payment Amount', ['default' => 100000, 'unit' => 'NPR']),
    num('tds_rate', 'TDS Rate', ['default' => 1.5, 'unit' => '%', 'max' => 25, 'step' => 0.1]),
]), <<<'CODE'
        $payment = $this->requireNumeric($inputs, 'payment');
        $tds = $payment * $this->requireNumeric($inputs, 'tds_rate') / 100;
        return [
            'results' => ['tds_amount' => $this->round($tds), 'net_payable' => $this->round($payment - $tds)],
            'breakdown' => ['note' => 'Enter the applicable TDS rate for the payment type'],
            'units' => ['tds_amount' => 'NPR', 'net_payable' => 'NPR'],
        ];
CODE);

$part5[] = item('nepse_brokerage_calculator', 'NepseBrokerageCalculator', 'nepal', 'NEPSE Brokerage Calculator', schema([
    num('transaction_amount', 'Transaction Amount', ['default' => 100000, 'unit' => 'NPR']),
    sel('side', 'Side', ['buy' => 'Buy', 'sell' => 'Sell'], 'buy'),
]), <<<'CODE'
        $amount = $this->requireNumeric($inputs, 'transaction_amount');
        // Simplified progressive brokerage sketch (illustrative)
        $brokerage = match (true) {
            $amount <= 50000 => $amount * 0.004,
            $amount <= 500000 => $amount * 0.0037,
            $amount <= 2000000 => $amount * 0.0034,
            default => $amount * 0.0027,
        };
        $sebon = $amount * 0.00015;
        $dp = 25;
        $totalCharges = $brokerage + $sebon + $dp;
        $side = $this->toString($inputs, 'side', 'buy');
        $net = $side === 'buy' ? $amount + $totalCharges : $amount - $totalCharges;
        return [
            'results' => [
                'brokerage' => $this->round($brokerage, 2),
                'sebon_fee' => $this->round($sebon, 2),
                'dp_fee' => $dp,
                'total_charges' => $this->round($totalCharges, 2),
                'net_amount' => $this->round($net, 2),
            ],
            'breakdown' => ['note' => 'Illustrative fee model — confirm with your broker’s latest tariff'],
            'units' => ['brokerage' => 'NPR', 'sebon_fee' => 'NPR', 'dp_fee' => 'NPR', 'total_charges' => 'NPR', 'net_amount' => 'NPR'],
        ];
CODE);

$part5[] = item('ipo_allotment_calculator', 'IpoAllotmentCalculator', 'nepal', 'IPO Allotment Probability', schema([
    num('total_kits', 'Total Kits Available', ['default' => 1000000, 'min' => 1, 'step' => 1]),
    num('total_applicants', 'Total Applicants', ['default' => 1500000, 'min' => 1, 'step' => 1]),
    num('your_kits', 'Kits You Applied', ['default' => 1, 'min' => 1, 'step' => 1]),
]), <<<'CODE'
        $kits = $this->requireNumeric($inputs, 'total_kits');
        $applicants = $this->requireNumeric($inputs, 'total_applicants');
        $yours = $this->requireNumeric($inputs, 'your_kits');
        $probability = min(100, $this->percentageOf($kits, $applicants));
        $expected = $applicants > 0 ? ($kits / $applicants) * $yours : 0;
        return [
            'results' => [
                'allotment_probability_percent' => $this->round($probability, 2),
                'expected_kits' => $this->round($expected, 4),
            ],
            'breakdown' => ['model' => 'Simple proportional estimate'],
            'units' => ['allotment_probability_percent' => '%', 'expected_kits' => 'kits'],
        ];
CODE);

$part5[] = item('nepal_house_cost_calculator', 'NepalHouseCostCalculator', 'nepal', 'Nepal House Construction Cost', schema([
    num('area_sqft', 'Built-up Area', ['default' => 1200, 'unit' => 'sq.ft']),
    num('cost_per_sqft', 'Cost Per sq.ft (NPR)', ['default' => 2800, 'unit' => 'NPR']),
    num('finishing', 'Finishing Contingency', ['default' => 15, 'unit' => '%', 'max' => 40]),
]), <<<'CODE'
        $base = $this->requireNumeric($inputs, 'area_sqft') * $this->requireNumeric($inputs, 'cost_per_sqft');
        $total = $base * (1 + $this->requireNumeric($inputs, 'finishing') / 100);
        return [
            'results' => [
                'base_cost_npr' => $this->round($base),
                'estimated_total_npr' => $this->round($total),
            ],
            'breakdown' => ['note' => 'Indicative only — rates vary by city, design and materials'],
            'units' => ['base_cost_npr' => 'NPR', 'estimated_total_npr' => 'NPR'],
        ];
CODE);

$part5[] = item('ropani_sqft_converter', 'RopaniSqftConverter', 'nepal', 'Ropani ↔ Square Feet', schema([
    num('value', 'Value', ['default' => 1, 'min' => 0]),
    sel('from_unit', 'From', ['ropani' => 'Ropani', 'sqft' => 'Square Feet'], 'ropani'),
]), <<<'CODE'
        // 1 Ropani = 5476 sq.ft (common Nepal convention)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'ropani');
        $sqft = $from === 'ropani' ? $value * 5476 : $value;
        $ropani = $sqft / 5476;
        return [
            'results' => [
                'ropani' => $this->round($ropani, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 2),
            ],
            'breakdown' => ['convention' => '1 Ropani = 5476 sq.ft'],
            'units' => ['ropani' => 'ropani', 'square_feet' => 'sq.ft', 'square_meters' => 'm²'],
        ];
CODE);

$part5[] = item('aana_sqm_converter', 'AanaSqmConverter', 'nepal', 'Aana ↔ Square Meter', schema([
    num('value', 'Value', ['default' => 1, 'min' => 0]),
    sel('from_unit', 'From', ['aana' => 'Aana', 'sqm' => 'Square Meter'], 'aana'),
]), <<<'CODE'
        // 1 Aana = 31.796 m² (approx; 1 Ropani = 16 Aana = 508.72 m²)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'aana');
        $sqm = $from === 'aana' ? $value * 31.796 : $value;
        $aana = $sqm / 31.796;
        return [
            'results' => [
                'aana' => $this->round($aana, 6),
                'square_meters' => $this->round($sqm, 3),
                'square_feet' => $this->round($sqm / 0.092903, 2),
            ],
            'breakdown' => ['convention' => '1 Aana ≈ 31.796 m²'],
            'units' => ['aana' => 'aana', 'square_meters' => 'm²', 'square_feet' => 'sq.ft'],
        ];
CODE);

$part5[] = item('dhur_converter', 'DhurConverter', 'nepal', 'Dhur Converter', schema([
    num('value', 'Value', ['default' => 1, 'min' => 0]),
    sel('from_unit', 'From', ['dhur' => 'Dhur', 'sqft' => 'Square Feet', 'sqm' => 'Square Meter'], 'dhur'),
]), <<<'CODE'
        // Terai: 1 Dhur ≈ 182.25 sq.ft (common)
        $value = $this->requireNumeric($inputs, 'value');
        $from = $this->toString($inputs, 'from_unit', 'dhur');
        $sqft = match ($from) {
            'sqm' => $value / 0.092903,
            'sqft' => $value,
            default => $value * 182.25,
        };
        return [
            'results' => [
                'dhur' => $this->round($sqft / 182.25, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 3),
            ],
            'breakdown' => ['convention' => '1 Dhur ≈ 182.25 sq.ft (Terai)'],
            'units' => ['dhur' => 'dhur', 'square_feet' => 'sq.ft', 'square_meters' => 'm²'],
        ];
CODE);

$part5[] = item('land_measurement_nepal_calculator', 'LandMeasurementNepalCalculator', 'nepal', 'Nepal Land Measurement', schema([
    num('ropani', 'Ropani', ['default' => 1, 'min' => 0, 'required' => false]),
    num('aana', 'Aana', ['default' => 0, 'min' => 0, 'max' => 15, 'required' => false]),
    num('paisa', 'Paisa', ['default' => 0, 'min' => 0, 'max' => 3, 'required' => false]),
    num('daam', 'Daam', ['default' => 0, 'min' => 0, 'max' => 3, 'required' => false]),
]), <<<'CODE'
        // 1 Ropani = 16 Aana = 64 Paisa = 256 Daam = 5476 sq.ft
        $ropani = $this->toFloat($inputs, 'ropani')
            + $this->toFloat($inputs, 'aana') / 16
            + $this->toFloat($inputs, 'paisa') / 64
            + $this->toFloat($inputs, 'daam') / 256;
        $sqft = $ropani * 5476;
        return [
            'results' => [
                'total_ropani' => $this->round($ropani, 6),
                'square_feet' => $this->round($sqft, 2),
                'square_meters' => $this->round($sqft * 0.092903, 2),
                'hectares' => $this->round($sqft * 0.092903 / 10000, 6),
            ],
            'breakdown' => ['system' => 'Hill system (Ropani–Aana–Paisa–Daam)'],
            'units' => ['total_ropani' => 'ropani', 'square_feet' => 'sq.ft', 'square_meters' => 'm²', 'hectares' => 'ha'],
        ];
CODE);

$part5[] = item('passport_fee_calculator', 'PassportFeeCalculator', 'nepal', 'Passport Fee Estimator', schema([
    sel('pages', 'Booklet', ['36' => '36 pages', '60' => '60 pages'], '36'),
    sel('urgency', 'Service', ['normal' => 'Normal', 'urgent' => 'Urgent'], 'normal'),
]), <<<'CODE'
        // Illustrative fee table — update when DoFE fees change
        $base = match ($this->toString($inputs, 'pages', '36')) {
            '60' => 10000,
            default => 5000,
        };
        if ($this->toString($inputs, 'urgency', 'normal') === 'urgent') {
            $base *= 2;
        }
        return [
            'results' => ['estimated_fee_npr' => $base],
            'breakdown' => ['note' => 'Indicative only — confirm on official DoFE / Department of Passport portal'],
            'units' => ['estimated_fee_npr' => 'NPR'],
        ];
CODE);

$part5[] = item('driving_license_fee_calculator', 'DrivingLicenseFeeCalculator', 'nepal', 'Driving License Fee Estimator', schema([
    sel('category', 'Category', ['a' => 'A (Motorcycle)', 'b' => 'B (Car)', 'both' => 'A + B'], 'b'),
    sel('type', 'Type', ['new' => 'New', 'renewal' => 'Renewal'], 'new'),
]), <<<'CODE'
        $fee = match ($this->toString($inputs, 'category', 'b')) {
            'a' => 1500,
            'both' => 3500,
            default => 2000,
        };
        if ($this->toString($inputs, 'type', 'new') === 'renewal') {
            $fee = (int) round($fee * 0.7);
        }
        return [
            'results' => ['estimated_fee_npr' => $fee],
            'breakdown' => ['note' => 'Indicative estimate — confirm with Transport Management Office'],
            'units' => ['estimated_fee_npr' => 'NPR'],
        ];
CODE);

$part5[] = item('gratuity_calculator', 'GratuityCalculator', 'nepal', 'Gratuity Calculator', schema([
    num('last_salary', 'Last Drawn Monthly Salary', ['default' => 50000, 'unit' => 'NPR']),
    num('years', 'Years of Service', ['default' => 10, 'min' => 0.5, 'max' => 50]),
]), <<<'CODE'
        // Common rule of thumb: 15 days salary × years (customize per labor act / contract)
        $salary = $this->requireNumeric($inputs, 'last_salary');
        $years = $this->requireNumeric($inputs, 'years');
        $gratuity = ($salary / 26) * 15 * $years;
        return [
            'results' => ['estimated_gratuity' => $this->round($gratuity)],
            'breakdown' => ['formula' => '(Monthly/26) × 15 × years — verify under applicable labor law'],
            'units' => ['estimated_gratuity' => 'NPR'],
        ];
CODE);

$part5[] = item('provident_fund_calculator', 'ProvidentFundCalculator', 'nepal', 'Provident Fund Calculator', schema([
    num('basic_salary', 'Monthly Basic Salary', ['default' => 40000, 'unit' => 'NPR']),
    num('employee_rate', 'Employee Contribution %', ['default' => 10, 'unit' => '%', 'max' => 20]),
    num('employer_rate', 'Employer Contribution %', ['default' => 10, 'unit' => '%', 'max' => 20]),
    num('years', 'Years', ['default' => 10, 'min' => 1, 'max' => 40]),
    num('annual_interest', 'Annual Interest %', ['default' => 6, 'unit' => '%', 'max' => 15]),
]), <<<'CODE'
        $basic = $this->requireNumeric($inputs, 'basic_salary');
        $monthly = $basic * (($this->requireNumeric($inputs, 'employee_rate') + $this->requireNumeric($inputs, 'employer_rate')) / 100);
        $months = (int) round($this->requireNumeric($inputs, 'years') * 12);
        $r = $this->requireNumeric($inputs, 'annual_interest') / 12 / 100;
        $fv = $r == 0.0 ? $monthly * $months : $monthly * (((1 + $r) ** $months - 1) / $r) * (1 + $r);
        return [
            'results' => [
                'monthly_contribution' => $this->round($monthly),
                'estimated_corpus' => $this->round($fv),
                'total_contributed' => $this->round($monthly * $months),
            ],
            'breakdown' => ['months' => $months],
            'units' => ['monthly_contribution' => 'NPR', 'estimated_corpus' => 'NPR', 'total_contributed' => 'NPR'],
        ];
CODE);

$part5[] = item('dashain_allowance_calculator', 'DashainAllowanceCalculator', 'nepal', 'Dashain Allowance Calculator', schema([
    num('basic_salary', 'Monthly Basic Salary', ['default' => 30000, 'unit' => 'NPR']),
    num('months', 'Allowance Months', ['default' => 1, 'min' => 0.5, 'max' => 2, 'step' => 0.5]),
]), <<<'CODE'
        $allowance = $this->requireNumeric($inputs, 'basic_salary') * $this->requireNumeric($inputs, 'months');
        return [
            'results' => ['dashain_allowance' => $this->round($allowance)],
            'breakdown' => ['note' => 'Often 1 month basic — confirm your organization policy'],
            'units' => ['dashain_allowance' => 'NPR'],
        ];
CODE);

return $part5;
