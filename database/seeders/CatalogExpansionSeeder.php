<?php

namespace Database\Seeders;

use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Support\CalculatorIconMap;
use Illuminate\Database\Seeder;

/**
 * Expands the public catalog with Finance / Health / Career tools from
 * research inventories. Missing engines use DynamicStubHandler via formula_key.
 *
 * Safe to re-run (upsert by slug). Does not remove existing calculators.
 */
class CatalogExpansionSeeder extends Seeder
{
    public int $added = 0;

    public int $updated = 0;

    public int $skipped = 0;

    public function run(): void
    {
        $categoryIds = $this->ensureCategories();

        foreach ($this->catalog() as $item) {
            $slug = $item['slug'];
            $formulaKey = str_replace('-', '_', $slug);
            $categoryId = $categoryIds[$item['category']] ?? $categoryIds['finance'];

            $existing = Calculator::withTrashed()->where('slug', $slug)->first();

            $payload = [
                'calculator_category_id' => $categoryId,
                'title' => $item['title'],
                'short_description' => $item['short'],
                'description' => $item['description'],
                'icon' => CalculatorIconMap::forCalculator($formulaKey) ?: 'bi-calculator',
                'formula_key' => $formulaKey,
                'formula_description' => $item['description'],
                'input_schema' => [
                    [
                        'name' => 'amount',
                        'label' => 'Primary Amount / Value',
                        'type' => 'number',
                        'unit' => null,
                        'required' => true,
                        'default' => 1000,
                        'min' => 0,
                        'step' => 0.01,
                    ],
                    [
                        'name' => 'rate',
                        'label' => 'Rate / Percent (optional)',
                        'type' => 'number',
                        'unit' => '%',
                        'required' => false,
                        'default' => 5,
                        'min' => 0,
                        'max' => 100,
                        'step' => 0.01,
                    ],
                ],
                'validation_rules' => [
                    'amount' => ['required', 'numeric', 'min:0'],
                    'rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
                ],
                'meta_title' => $item['title'].' | AI Calculator Hub',
                'meta_description' => $item['short'],
                'is_premium' => false,
                'is_featured' => false,
                'is_active' => true,
                'deleted_at' => null,
            ];

            if ($existing) {
                // Keep real engines if a dedicated handler already powers this slug.
                $hasRealHandler = class_exists(
                    'App\\Services\\Calculators\\Handlers\\'.$this->studly($formulaKey)
                );
                if ($hasRealHandler || $existing->formula_key !== $formulaKey && $existing->input_schema) {
                    // Only refresh copy if this was already a catalog stub or empty description.
                    $existing->fill([
                        'title' => $item['title'],
                        'short_description' => $item['short'],
                        'description' => $item['description'] ?: $existing->description,
                        'meta_title' => $payload['meta_title'],
                        'meta_description' => $payload['meta_description'],
                        'is_active' => true,
                        'deleted_at' => null,
                    ]);
                    $existing->save();
                    $this->updated++;
                    continue;
                }

                $existing->fill($payload)->save();
                $this->updated++;
                continue;
            }

            Calculator::query()->create(array_merge($payload, [
                'slug' => $slug,
                'sort_order' => 500 + $this->added,
                'views_count' => 0,
                'usage_count' => 0,
            ]));
            $this->added++;
        }

        $this->command?->info("CatalogExpansionSeeder: added={$this->added}, updated={$this->updated}");
    }

    /**
     * @return array<string, int>
     */
    protected function ensureCategories(): array
    {
        $defs = [
            [
                'slug' => 'finance',
                'name' => 'Finance Calculators',
                'sort_order' => 2,
                'description' => 'Loans, investing, wealth, daily money and forex calculators.',
            ],
            [
                'slug' => 'health',
                'name' => 'Health & Fitness Calculators',
                'sort_order' => 3,
                'description' => 'Body metrics, nutrition, pregnancy and wellness cost tools.',
            ],
            [
                'slug' => 'fitness',
                'name' => 'Fitness Calculators',
                'sort_order' => 10,
                'description' => 'Training, pace, heart-rate and performance calculators.',
            ],
            [
                'slug' => 'career',
                'name' => 'Career & Pay Calculators',
                'sort_order' => 18,
                'description' => 'Take-home pay, freelance rates, job offers and career decision tools.',
                'meta_title' => 'Career & Pay Calculators | AI Calculator Hub',
                'meta_description' => 'Free career calculators for salary, take-home pay, freelance rates, job offers and cost of living.',
            ],
            [
                'slug' => 'business',
                'name' => 'Business Calculators',
                'sort_order' => 5,
                'description' => 'Business, tax and commercial calculators.',
            ],
            [
                'slug' => 'developer',
                'name' => 'AI & Developer Tools',
                'sort_order' => 16,
                'description' => 'API and token cost estimators for developers.',
            ],
            [
                'slug' => 'real-estate',
                'name' => 'Real Estate Calculators',
                'sort_order' => 14,
                'description' => 'Mortgage, home and property decision tools.',
            ],
            [
                'slug' => 'daily-life',
                'name' => 'Daily Life Calculators',
                'sort_order' => 7,
                'description' => 'Date, time, age and everyday utility calculators.',
            ],
            [
                'slug' => 'basic-math',
                'name' => 'Basic Math Calculators',
                'sort_order' => 9,
                'description' => 'Everyday math, percentages, ratios and statistics.',
            ],
            [
                'slug' => 'education',
                'name' => 'Education Calculators',
                'sort_order' => 4,
                'description' => 'GPA, grades, word count and school tools.',
            ],
            [
                'slug' => 'unit-conversion',
                'name' => 'Unit Conversion Calculators',
                'sort_order' => 6,
                'description' => 'Length, weight, volume, temperature and specialty converters.',
            ],
            [
                'slug' => 'life-decisions',
                'name' => 'Life & Decision Calculators',
                'sort_order' => 19,
                'description' => 'Career pivots, family, relocation and long-game life decision tools.',
                'meta_title' => 'Life & Decision Calculators | AI Calculator Hub',
                'meta_description' => 'Free calculators for quitting, school ROI, family costs, moves and long-term life decisions.',
            ],
            [
                'slug' => 'retirement',
                'name' => 'Retirement Calculators',
                'sort_order' => 20,
                'description' => 'Social Security, RMD, Roth ladder, HSA, pension and Medicare tools.',
                'meta_title' => 'Retirement Calculators | AI Calculator Hub',
                'meta_description' => 'Free retirement calculators for claiming age, RMD, Roth conversions, annuities, HSA and Medicare.',
            ],
            [
                'slug' => 'construction',
                'name' => 'Construction Calculators',
                'sort_order' => 1,
                'description' => 'Materials estimating, home improvement and outdoor project calculators.',
            ],
            [
                'slug' => 'home',
                'name' => 'Home Calculators',
                'sort_order' => 11,
                'description' => 'Home improvement cost and materials planning tools.',
            ],
            [
                'slug' => 'automobile',
                'name' => 'Automobile Calculators',
                'sort_order' => 12,
                'description' => 'Ownership cost, refinance, tire size, lease vs buy, EV vs gas and commute calculators.',
                'meta_title' => 'Automobile & Commute Calculators | AI Calculator Hub',
                'meta_description' => 'Free auto calculators for true cost per mile, refinance, tire size, lease vs buy, EV vs gas and commute cost.',
            ],
            [
                'slug' => 'climate-energy',
                'name' => 'Climate & Energy Calculators',
                'sort_order' => 13,
                'description' => 'Solar, storage, HVAC electrification, carbon footprint and climate-risk decision tools.',
                'meta_title' => 'Climate & Energy Calculators | AI Calculator Hub',
                'meta_description' => 'Free climate and energy calculators for solar ROI, heat pumps, EV vs ICE TCO, carbon footprint and climate migration.',
            ],
            [
                'slug' => 'tax-deductions',
                'name' => 'Tax & Deductions Calculators',
                'sort_order' => 14,
                'description' => 'US federal tax planning, equity compensation, and multi-country tax tools.',
                'meta_title' => 'Tax & Deductions Calculators | AI Calculator Hub',
                'meta_description' => 'Free tax calculators for Roth strategies, RSU tax, capital gains, quarterly estimates and nomad residency.',
            ],
            [
                'slug' => 'productivity',
                'name' => 'Productivity Calculators',
                'sort_order' => 15,
                'description' => 'Habits, decision quality, cognitive load and timing tools.',
                'meta_title' => 'Productivity Calculators | AI Calculator Hub',
                'meta_description' => 'Free productivity calculators for compound habits, success odds, cognitive load and decision fatigue.',
            ],
        ];

        $ids = [];
        foreach ($defs as $def) {
            $model = CalculatorCategory::query()->updateOrCreate(
                ['slug' => $def['slug']],
                [
                    'name' => $def['name'],
                    'icon' => CalculatorIconMap::forCategory($def['slug']),
                    'description' => $def['description'],
                    'meta_title' => $def['meta_title'] ?? ($def['name'].' | AI Calculator Hub'),
                    'meta_description' => $def['meta_description'] ?? $def['description'],
                    'sort_order' => $def['sort_order'],
                    'is_active' => true,
                ]
            );
            $ids[$def['slug']] = $model->id;
        }

        // Ensure all existing categories are mapped too.
        foreach (CalculatorCategory::query()->get(['id', 'slug']) as $cat) {
            $ids[$cat->slug] = $cat->id;
        }

        return $ids;
    }

    protected function studly(string $formulaKey): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $formulaKey)));
    }

    /**
     * @return array<int, array{slug: string, title: string, category: string, short: string, description: string}>
     */
    protected function catalog(): array
    {
        $items = [];

        $add = function (string $slug, string $title, string $category, string $short, ?string $description = null) use (&$items) {
            $items[] = [
                'slug' => $slug,
                'title' => $title,
                'category' => $category,
                'short' => $short,
                'description' => $description ?: $short,
            ];
        };

        // ── Finance: Investing & Wealth ──────────────────────────────
        $add('compound-interest-calculator', 'Compound Interest Calculator', 'finance', 'Project final balance on a lump sum, monthly contribution, or both.');
        $add('retirement-savings-calculator', 'Retirement Savings Calculator', 'finance', 'Project your retirement nest egg with monthly compounding and FIRE number.');
        $add('investment-roi-calculator', 'Investment ROI Calculator', 'finance', 'Total ROI %, dollar gain, and annualized return from cost, fees and final value.');
        $add('net-worth-calculator', 'Net Worth Calculator', 'finance', 'Assets minus debts across buckets for a clear net-worth snapshot.');
        $add('inflation-calculator', 'Inflation Calculator', 'finance', 'Future or past purchasing power of money over N years.');
        $add('fire-monte-carlo-calculator', 'FIRE Monte Carlo Calculator', 'finance', 'Retirement success probability with contribution, withdrawal and return assumptions.');
        $add('property-flip-roi-calculator', 'Property Flip ROI Calculator', 'finance', 'Purchase, ARV, rehab and holding costs to estimate flip ROI.');
        $add('pension-lump-sum-vs-annuity-calculator', 'Pension Lump Sum vs Annuity Calculator', 'finance', 'Compare lump-sum offer vs monthly annuity with longevity assumptions.');
        $add('statistical-life-value-calculator', 'Statistical Life Value Calculator', 'finance', 'Income-based statistical value of remaining working years.');
        $add('airbnb-vs-long-term-rental-calculator', 'Airbnb vs Long-Term Rental Calculator', 'finance', 'STR vs LTR cash-flow comparison for the same property.');
        $add('crypto-real-yield-calculator', 'Crypto Real Yield Calculator', 'finance', 'APY after fees, gas and inflation for crypto yield positions.');
        $add('crypto-tax-lot-optimizer', 'Crypto Tax Lot Optimizer', 'finance', 'Optimize which lots to sell for tax-aware crypto exits.');
        $add('roth-vs-traditional-401k-calculator', 'Roth vs Traditional 401(k) Calculator', 'finance', 'Apples-to-apples Roth vs Traditional retirement contribution comparison.');
        $add('social-security-break-even-calculator', 'Social Security Break-Even Calculator', 'finance', 'Claim-age comparison for 62 vs 67 vs 70 with actuarial adjustments.');
        $add('tax-loss-harvesting-calculator', 'Tax-Loss Harvesting Calculator', 'finance', 'Harvest unrealized losses against planned gains at your tax rate.');
        $add('apy-calculator', 'APY Calculator', 'finance', 'Convert APR (nominal) to APY (effective) at any compounding frequency.');

        // ── Finance: Daily Money ─────────────────────────────────────
        $add('budget-calculator', 'Budget Calculator', 'finance', 'Compare take-home vs needs/wants/savings against the 50/30/20 ideal.');
        $add('life-insurance-needs-calculator', 'Life Insurance Needs Calculator', 'finance', 'Coverage need from income, dependents, mortgage and education plans.');
        $add('cost-of-raising-a-child-calculator', 'Cost of Raising a Child Calculator', 'finance', 'Lifetime childcare-to-college cost estimate by country and path.');
        $add('break-even-calculator', 'Break-Even Calculator', 'finance', 'Units and revenue needed to cover fixed and variable costs.');
        $add('wedding-budget-optimizer-calculator', 'Wedding Budget Optimizer Calculator', 'finance', 'Allocate wedding budget across venue, catering and major categories.');
        $add('can-i-afford-this-calculator', 'Can I Afford This? Calculator', 'finance', 'YES / NO / MAYBE affordability from price, income and expenses.');
        $add('subscription-audit-calculator', 'Subscription Audit Calculator', 'finance', 'Annual drain from monthly subscriptions with cancel recommendations.');
        $add('pet-ownership-lifetime-cost-calculator', 'Pet Ownership Lifetime Cost Calculator', 'finance', 'Lifetime food, vet and care cost by species and lifespan.');
        $add('travel-rewards-card-optimizer', 'Travel Rewards Card Optimizer', 'finance', 'Compare travel cards on net annual value from your spend mix.');
        $add('divorce-financial-cost-calculator', 'Divorce Financial Cost Calculator', 'finance', 'Estimate legal and asset-split costs by contest type.');
        $add('pet-insurance-breakeven-calculator', 'Pet Insurance Breakeven Calculator', 'finance', 'Premium vs expected vet bills and self-insure decision.');
        $add('cobra-vs-aca-marketplace-calculator', 'COBRA vs ACA Marketplace Calculator', 'finance', 'COBRA total cost vs ACA premium with subsidy estimates.');
        $add('disability-insurance-need-calculator', 'Disability Insurance Need Calculator', 'finance', 'Recommended disability coverage from income and dependents.');
        $add('cashback-vs-travel-card-breakeven', 'Cashback vs Travel Card Breakeven', 'finance', 'Which card wins given spend, travel and redemption habits.');
        $add('umbrella-insurance-coverage-calculator', 'Umbrella Insurance Coverage Calculator', 'finance', 'Recommended umbrella limit by net worth and lifestyle risk.');
        $add('long-term-care-insurance-breakeven-calculator', 'Long-Term Care Insurance Breakeven Calculator', 'finance', 'Premium NPV vs probability-weighted LTC costs.');
        $add('buy-now-vs-save-first-calculator', 'Buy Now vs Save First Calculator', 'finance', 'Finance now vs save-then-buy with the same monthly amount.');
        $add('currency-converter', 'Currency Converter', 'finance', 'Convert between major world currencies with live-rate ready inputs.');

        // ── Finance / Real Estate: Loans & Housing ───────────────────
        $add('loan-emi-calculator', 'Loan EMI Calculator', 'finance', 'Monthly EMI, total interest and extra-payment savings for any loan.');
        $add('mortgage-calculator', 'Mortgage Calculator', 'finance', 'Full monthly mortgage payment with tax, insurance and PMI.');
        $add('debt-payoff-calculator', 'Debt Payoff Calculator', 'finance', 'Month-by-month payoff with avalanche/snowball extra payments.');
        $add('house-affordability-beyond-dti-calculator', 'House Affordability Calculator (Beyond DTI)', 'real-estate', 'House budget from income, debts, down payment, tax and insurance.');
        $add('credit-card-payoff-calculator', 'Credit Card Payoff Calculator', 'finance', 'Time and interest to clear a balance at a given APR and payment.');
        $add('buy-vs-rent-true-cost-calculator', 'Buy vs Rent True Cost Calculator', 'real-estate', 'True ownership vs renting cost over your holding period.');
        $add('student-loan-pslf-save-eligibility', 'Student Loan PSLF / SAVE Eligibility', 'finance', 'Check PSLF and SAVE income-driven repayment eligibility.');
        $add('should-i-sell-my-house-calculator', 'Should I Sell My House in 2026?', 'real-estate', 'Net wealth projection selling now vs holding 5/10/15 years.');
        $add('adu-build-roi-calculator', 'ADU Build ROI', 'real-estate', 'Cash flow and ROI for building an accessory dwelling unit.');
        $add('closing-cost-calculator', 'Closing Cost Calculator', 'real-estate', 'CFPB-style closing cost estimate by price, state and loan type.');
        $add('pmi-removal-date-calculator', 'PMI Removal Date Calculator', 'real-estate', 'When loan hits 80% and 78% LTV to cancel PMI.');
        $add('amortization-schedule-calculator', 'Amortization Schedule Calculator', 'finance', 'Full monthly amortization from amount, rate and term.');
        $add('mortgage-refinance-calculator', 'Mortgage Refinance Calculator', 'real-estate', 'Does refinancing pay for itself over your remaining stay?');
        $add('payoff-mortgage-early-vs-invest-calculator', 'Pay-Off Mortgage Early vs Invest Calculator', 'finance', 'Extra mortgage payments vs investing the same cash.');
        $add('home-affordability-calculator', 'Home Affordability Calculator', 'real-estate', 'Simple affordability from income, debts and interest rate.');
        $add('va-loan-calculator', 'VA Loan Calculator', 'real-estate', 'VA loan payment with funding fee and disability waiver toggles.');
        $add('reverse-mortgage-calculator', 'Reverse Mortgage Calculator', 'real-estate', 'Estimate equity unlock and upfront reverse-mortgage costs.');
        $add('cash-out-refinance-calculator', 'Cash-Out Refinance Calculator', 'real-estate', 'New loan amount from home value, balance and rate.');
        $add('rv-loan-calculator', 'RV Loan Calculator', 'finance', 'Monthly RV loan payment with down payment and rate.');
        $add('boat-loan-calculator', 'Boat Loan Calculator', 'finance', 'Boat loan payment with optional insurance in TCO.');

        // ── Automobile & Commute ─────────────────────────────────────
        $add(
            'true-cost-per-mile-calculator',
            'True Cost Per Mile Calculator',
            'automobile',
            'Most drivers underestimate by 50%+ because they only count fuel. This calculator forces fuel + insurance + maintenance + depreciation into the picture, then compares against the IRS $0.67/mi standard.'
        );
        $add(
            'auto-refinance-calculator',
            'Auto Refinance Calculator',
            'automobile',
            'Compare your current auto loan vs a refinance offer: monthly payment savings, breakeven month on the fees, total interest savings over the loan life, and a verdict score on whether the refi actually pencils out.'
        );
        $add(
            'tire-size-calculator',
            'Tire Size Calculator',
            'automobile',
            'Drop your tire size (width × aspect × rim) and reference — get sidewall height, overall diameter, revolutions per mile, and speedometer error vs reference. Built on TRA + ETRTO P-metric sizing standards.'
        );
        $add(
            'lease-vs-buy-car-calculator',
            'Lease vs Buy Car Calculator',
            'automobile',
            'Compare buying vs leasing the same car over your actual stay length. The calculator shows total cost both ways + the break-even year — the ownership length where buying flips from worse to cheaper.'
        );
        $add(
            'ev-vs-gas-total-cost-calculator',
            'EV vs Gas Total Cost Calculator',
            'automobile',
            'Compare an EV against a gas car over your ownership window. The calculator shows annual operating savings, upfront premium recovery time, and net dollar savings — with every assumption editable.'
        );
        $add(
            'commute-cost-time-calculator',
            'Commute Cost + Time Calculator',
            'automobile',
            'What your commute actually costs — vehicle (fuel + wear + parking) plus the lost-time cost at your hourly wage. Most calcs only show fuel; this one shows the full bill, then tells you how much remote work would save.'
        );

        // ── Climate & Energy ─────────────────────────────────────────
        $add(
            'solar-roi-calculator',
            'Solar ROI Calculator',
            'climate-energy',
            'Drop your monthly bill, local kWh tariff, roof area, panel tier, battery option, financing, net-metering policy, electricity-price appreciation, panel degradation, and time horizon. Calculator sizes the system to your usage (or roof, whichever is smaller), reflects the 2026 expiry of the IRS Section 25D 30% federal credit (no federal credit for installs after Dec 31 2025 under the One Big Beautiful Bill), simulates production with degradation and tariff escalation, and returns payback period, 20-yr savings, IRR, battery payback delta, and lifetime CO₂ avoided. Anchored to NREL PVWatts insolation, LBNL field-study degradation curves, and EIA grid-emissions data.'
        );
        $add(
            'backup-power-roi-calculator',
            'Backup Power ROI (Powerwall vs Generator)',
            'climate-energy',
            '10-year NPV per option with IRA 30% credit, comfort/safety value-add, recommended configuration. WFH outage cost monetized.'
        );
        $add(
            'solar-panel-cost-calculator',
            'Solar Panel Cost Calculator',
            'climate-energy',
            'Drop monthly bill, state, roof orientation, and electric rate — get sized system in kW, total installed cost, payback years, and 25-year net savings. Note: the 30% federal Residential Clean Energy Credit (IRS §25D) expired Dec 31 2025 (One Big Beautiful Bill) and is $0 for 2026 installs — state / utility incentives may still apply. For full IRR + battery + NEM modeling, see the Solar ROI calculator.'
        );
        $add(
            'ev-vs-ice-tco-calculator',
            'EV vs ICE TCO Calculator',
            'climate-energy',
            'Drop EV + ICE prices, annual miles, electricity rate, gas price, gas trajectory, maintenance + insurance deltas, EV tax credit, and 5-yr resale delta. Calculator computes 5-year total cost of ownership for both vehicles, surfaces fuel / maintenance / insurance / resale gaps, and finds the electricity rate at which the comparison flips. Note: the federal $7,500 Section 30D EV credit expired Sept 30 2025 (One Big Beautiful Bill) — it defaults to $0 for 2026; enter a state incentive if you qualify. Anchored to AAA Driving Cost study, NAIC insurance data, and KBB / BLS resale benchmarks.'
        );
        $add(
            'ac-size-calculator',
            'AC Sizing + Cost Calculator',
            'climate-energy',
            'Drop room sq ft, ceiling height, climate zone, sun exposure, occupants, insulation rating, electricity rate, and your considered SEER tier. Calculator computes the right-sized BTU/hr (Manual J Square-foot-method approximation), rounds up to nearest 0.5 ton, and compares install + 10-yr operating cost across SEER 14 / 17 / 22 efficiency tiers — recommending the tier with lowest total cost of ownership and surfacing SEER 22 vs SEER 17 marginal-payback. Anchored to ACCA Manual J 8th Ed sizing standard, DOE 2023 SEER2 ratings, and EIA cooling-degree-day data.'
        );
        $add(
            'heat-pump-payback-calculator',
            'Heat Pump Payback Calculator',
            'climate-energy',
            'Drop your current heating fuel + annual heating bill, climate zone, home sq ft, insulation rating, electric rate, install cost, and tax credit. Calculator converts your bill into BTUs delivered, applies the climate-zone-appropriate seasonal COP for an air-source heat pump, computes operating cost + annual savings + payback period, and surfaces a fuel-trajectory-accelerated payback row plus an insulation-upgrade opportunity hint when implied heating demand exceeds the norm. Anchored to DOE EnergyStar field studies, AHRI 210/240 testing, EIA fuel + electricity data, and IRS Section 25C / IRA HEEHRA rebate programs.'
        );
        $add(
            'home-insulation-roi-calculator',
            'Home Insulation ROI Calculator',
            'climate-energy',
            'Drop home sq ft, current insulation R-value, target R-value, climate zone, current heating + cooling bill, install cost, and fuel-price growth. Calculator computes heat-loss reduction (1 − R_current/R_target), bill savings, payback period, 20-year lifetime savings with fuel appreciation, and IRR. Surfaces a tier-progression hint when current target leaves meaningful additional savings on the table. Anchored to DOE Building America + IECC 2021 R-value targets, LBNL field studies of envelope retrofits, and IRS Section 25C 30%-up-to-$1,200/yr credit.'
        );
        $add(
            'whole-home-electrification-bundle-roi-calculator',
            'Whole-Home Electrification Bundle ROI',
            'climate-energy',
            'Heat pump + induction + EV charger + solar + battery — bundled vs sequential 25-yr NPV. IRA Sections 25C / 25D / 30C credits, low-income HEEHRA boost, panel upgrade trigger.'
        );
        $add(
            'flood-insurance-vs-self-insure-calculator',
            'Flood Insurance vs Self-Insure Calculator',
            'climate-energy',
            'Expected annual loss × FEMA flood zone probability. NFIP vs private compare. NFIP coverage gap on high-value homes.'
        );
        $add(
            'home-climate-hardening-payback-calculator',
            'Home Climate-Hardening Payback Calculator',
            'climate-energy',
            'Per-measure payback by peril (hurricane/wildfire/flood/hail). Insurance discount + damage mitigation + resale lift. Phased ROI ranking.'
        );
        $add(
            'wildfire-defensible-space-roi-calculator',
            'Wildfire Defensible Space ROI',
            'climate-energy',
            'Annual expected-loss reduction. NPV 10-year. Insurance non-renewal flag for high-WUI zones. Cal Fire Zone 0/1/2 compliance.'
        );
        $add(
            'carbon-footprint-true-cost-calculator',
            'Carbon Footprint True Cost Calculator',
            'climate-energy',
            'Drop your flight tier, annual miles driven, diet, home size, electricity source, electronics replacement, shopping intensity, and preferred offset price. Calculator aggregates emissions across 7 buckets to compute annual CO₂e tonnage, compares to US median 16 t, costs the offset, and ranks the top 3 reduction levers by absolute t saved (with cost-to-implement framing — most are $0/t or save money). Anchored to EPA GHG Inventory, Project Drawdown research, Poore-Nemecek 2018 Science (diet emissions), and ICAO emissions database (flight cabin-class multipliers).'
        );
        $add(
            'flight-emissions-offset-calculator',
            'Flight Emissions + Offset Calculator',
            'climate-energy',
            'Drop your route distance, route label, cabin class (economy / premium economy / business / first), round-trips per year, and offset price tier. Calculator computes annual CO₂e from your flights using ICAO basic-CO₂ methodology, surfaces a separate radiative-forcing-inclusive (Lee et al. 2021) sensitivity row, compares to the economy-class alternative (showing cabin penalty), and costs the offset at your chosen quality tier. Anchored to ICAO 2024 emissions methodology, Lee et al. 2021 atmospheric science, and Atmosfair / Verra / Gold Standard offset pricing.'
        );
        $add(
            'electricity-bill-optimizer-tou-calculator',
            'Electricity Bill Optimizer (TOU)',
            'climate-energy',
            'Drop your monthly kWh, peak / off-peak TOU rates, EV charging time, appliance flexibility tier, heating type, willingness to shift hrs/wk, and battery option. Calculator computes manual-shift savings (moving load from peak → off-peak via thermostat scheduling, dishwasher / dryer / hot-water timing, EV charging window) and a separate battery-arbitrage scenario (battery charges off-peak, discharges peak — full TOU spread captured automatically). Recommends whether battery storage pays back at your specific TOU spread + bill size. Anchored to California NEM 3.0 + EV-2A rate schedules, DOE residential battery field studies, and IRS Section 25D 30% residential clean energy credit.'
        );
        $add(
            'climate-migration-cost-calculator',
            'Climate Migration Cost Calculator',
            'climate-energy',
            '10-year financial delta moving from peril-exposed state to lower-risk state. Move cost, COL delta, insurance delta, peril-risk monetized, emotional adjustment.'
        );

        // ── Tax & Deductions ─────────────────────────────────────────
        $add(
            'kids-529-vs-utma-vs-roth-calculator',
            '529 vs UTMA vs Roth (Kids) Calculator',
            'tax-deductions',
            'Vehicle-by-vehicle growth + tax + FAFSA financial-aid impact. State 529 deduction lookup. SECURE 2.0 unused-529-to-Roth provision.'
        );
        $add(
            'multi-state-remote-work-tax-exposure-calculator',
            'Multi-State Remote Work Tax Exposure',
            'tax-deductions',
            'Domicile state + every state you worked from — total tax owed, double-taxation exposure, NY/CT/DE/NE/PA convenience-rule penalty, recommended record-keeping.'
        );
        $add(
            'hsa-triple-tax-optimizer-calculator',
            'HSA Triple-Tax Optimizer',
            'tax-deductions',
            'Project HSA growth at retirement vs Trad 401(k), Roth 401(k), and taxable. Optimal shoebox-method receipt-storage timeline.'
        );
        $add(
            'bonus-tax-calculator',
            'Bonus Tax Calculator',
            'tax-deductions',
            'Enter your salary + bonus + state. The calculator shows the supplemental flat withholding (22% federal) versus your real marginal tax — and tells you whether you\'ll get money back at filing or owe more.'
        );
        $add(
            'mega-backdoor-roth-calculator',
            'Mega-Backdoor Roth Calculator',
            'tax-deductions',
            'Check eligibility (after-tax + in-plan/in-service), compute this year\'s after-tax headroom, and project lifetime Roth growth vs taxable.'
        );
        $add(
            'backdoor-roth-pro-rata-trap-calculator',
            'Backdoor Roth + Pro-Rata Trap Calculator',
            'tax-deductions',
            'Compute pro-rata tax bill if Trad IRA pre-tax balances trigger aggregation. Mitigation: roll pre-tax to 401(k) first.'
        );
        $add(
            'property-tax-calculator',
            'Property Tax Calculator',
            'tax-deductions',
            'Drop home value + state — get your effective property-tax rate (US Census 2022 medians), annual bill, monthly escrow, and how it compares to the national and state averages. Optional county-rate override for hyperlocal accuracy.'
        );
        $add(
            'quarterly-estimated-tax-calculator',
            'Quarterly Estimated Tax Calculator',
            'tax-deductions',
            'Plug in your projected income, withholding, and last year\'s numbers. The calculator applies the IRS safe harbor and tells you exactly what to send by April 15 — and the same amount each following quarter.'
        );
        $add(
            'dependent-care-fsa-vs-child-tax-credit-calculator',
            'Dependent Care FSA vs Child Tax Credit Calculator',
            'tax-deductions',
            'Optimal DCFSA contribution + residual claimed via CDCC (Form 2441). Total tax saved this year vs CDCC alone.'
        );
        $add(
            'capital-gains-tax-calculator',
            'Capital Gains Tax Calculator',
            'tax-deductions',
            'Drop cost basis, sale price, holding period, AGI, filing status, and optional state rate — get federal capital-gains tax (short-term at ordinary brackets, long-term at 0/15/20%), NIIT 3.8% if applicable, and the held-vs-sold counterfactual showing what you\'d save if you held to 12 months.'
        );
        $add(
            'after-tax-income-calculator',
            'After-Tax Income Calculator',
            'tax-deductions',
            'Drop gross income, filing status, state, and any 401(k)/HSA pre-tax contributions — get federal + state + FICA + Medicare stacked into one annual after-tax number, with monthly + bi-weekly + weekly breakdowns. Pre-tax deductions surfaced as a separate line so you can see the lever.'
        );
        $add(
            'rsu-tax-withholding-shortfall-calculator',
            'RSU Tax & Withholding Shortfall Calculator',
            'tax-deductions',
            'Enter your salary + RSU vest + YTD prior vests. The calculator shows the 22% supplemental flat withholding vs your real marginal tax — and the cumulative shortfall across the year that triggers FAANG-tier April balances.'
        );
        $add(
            'stock-options-iso-nso-amt-calculator',
            'Stock Options ISO/NSO + AMT Calculator',
            'tax-deductions',
            'Toggle between ISO and NSO. The calculator runs the IRS 2026 federal brackets + Form 6251 AMT math + FICA — and tells you exactly how much tax you owe at exercise, plus the cash you actually need on hand.'
        );
        $add(
            'tax-bracket-calculator',
            'Tax Bracket Calculator',
            'tax-deductions',
            'Pick country + income. The calculator shows your marginal rate (the bracket your NEXT dollar lands in), your effective rate (the blended rate across all dollars), and what a $5k raise actually nets after federal income tax.'
        );
        $add(
            'digital-nomad-tax-residency-optimizer-calculator',
            'Digital Nomad Tax Residency Optimizer',
            'tax-deductions',
            'Compare US-stay vs FEIE (Form 2555) vs FTC route across 12+ nomad destinations. Bona Fide vs Physical Presence eligibility, state shedding, treaty offsets, self-employment tax — surfaces best route + savings vs baseline.'
        );

        // ── Productivity ─────────────────────────────────────────────
        $add(
            'compound-habit-calculator',
            'Compound Habit Calculator',
            'productivity',
            'Drop a habit, the daily improvement rate (% compounded), the time horizon, and your current baseline. Calculator computes the compounding multiplier (the famous Atomic Habits 37.8× framing for 1%/day over 1 yr), surfaces 1-yr / 5-yr / 10-yr trajectories, and frames the result in habit-specific cumulative units (reading hours → books, writing words → novels, language minutes → CEFR fluency).'
        );
        $add(
            'probability-of-success-calculator',
            'Probability of Success Calculator',
            'productivity',
            'Drop your project type, prior experience, team size, runway, and timeline realism. Calculator anchors against published base rates (cold-start SaaS 4%, restaurants 30%, books 8%, agencies 22%, etc.) and adjusts for your specific personal advantages — surfacing your honest adjusted probability and the single biggest risk in the math.'
        );
        $add(
            'cognitive-load-calculator',
            'Cognitive Load Calculator',
            'productivity',
            'Drop your active projects, daily decisions, open todos, weekly meetings, daily notifications, and 7-day average sleep. Calculator computes a 0-100 cognitive-load score against published thresholds (Miller 7-item working memory, Bargh & Vohs 60-decision ceiling, Atlassian 23-min interruption-recovery, Walker sleep literature), identifies your top drag, and recommends the single highest-leverage reclaim.'
        );
        $add(
            'best-day-to-move-calculator',
            'Best Day to Move Calculator',
            'productivity',
            'Drop your city tier, current lease-end month, flexibility window, unit size, and negotiation propensity. Calculator surfaces the optimal mid-month move date in your city — rents drop 6-12% in winter for tier-1 metros (NYC, SF, LA, Boston, DC, Chicago) — and computes annual savings vs the worst-month alternative within your flex window.'
        );
        $add(
            'decision-fatigue-calculator',
            'Decision Fatigue Calculator',
            'productivity',
            'Drop your daily conscious-decision count, trivial/meaningful split, scheduled-routine %, mental-rest blocks, alcohol/wk, and average sleep. Calculator surfaces effective decisions after routine, rest recovery, alcohol + sleep penalties, and the specific hour of the day your decision quality crashes — calibrated against Bargh & Vohs / Vohs & Heatherton glucose-willpower research and Walker sleep literature.'
        );

        // ── Health: Nutrition ────────────────────────────────────────
        $add('calorie-tdee-calculator', 'Calorie / TDEE Calculator', 'health', 'Mifflin-St Jeor BMR, TDEE and macro split for your goal.');
        $add('water-intake-calculator', 'Water Intake Calculator', 'health', 'Daily water target from bodyweight, activity and climate.');
        $add('macro-calculator', 'Macro Calculator', 'health', 'Daily calorie target plus protein / fat / carbs in grams.');
        $add('protein-intake-calculator', 'Protein Intake Calculator', 'health', 'Daily protein target from bodyweight, goal and meal frequency.');
        $add('bac-calculator', 'BAC Calculator', 'health', 'Estimate blood alcohol content from drinks, weight and time.');
        $add('macros-cutting-bulking-calculator', 'Macros Cutting / Bulking Calculator', 'health', 'Cut, maintain or bulk macros from stats and activity.');
        $add('alcohol-cost-calculator', 'Alcohol Cost Calculator', 'health', 'Weekly drink spend projected vs investing the same money.');
        $add('nutrient-density-calculator', 'Nutrient Density Calculator', 'health', 'Score daily food quality across whole foods vs ultra-processed.');

        // ── Fitness ──────────────────────────────────────────────────
        $add('sleep-cycle-calculator', 'Sleep Cycle Calculator', 'fitness', 'Bedtime options in 90-minute cycles for a target wake time.');
        $add('heart-rate-zone-calculator', 'Heart Rate Zone Calculator', 'fitness', 'Max HR and five training zones from age and resting HR.');
        $add('running-pace-calculator', 'Running Pace Calculator', 'fitness', 'Solve distance, time or pace from any two inputs.');
        $add('one-rep-max-calculator', 'One-Rep Max Calculator', 'fitness', 'Estimate 1RM from a sub-maximal set with validated formulas.');
        $add('sleep-debt-calculator', 'Sleep Debt Calculator', 'fitness', 'Accumulated sleep debt from target vs actual sleep.');
        $add('vo2max-target-calculator', 'VO2max Target Calculator', 'fitness', 'Field-test VO2max estimate and training targets.');
        $add('wearable-stack-roi-calculator', 'Wearable Stack ROI Calculator', 'fitness', 'Redundancy and ROI across Oura, Whoop and watch stacks.');
        $add('exercise-equivalent-calculator', 'Exercise Equivalent Calculator', 'fitness', 'Minutes of common exercises to burn a calorie target.');
        $add('treadmill-calorie-calculator', 'Treadmill Calorie Calculator', 'fitness', 'ACSM walking/running calories burned on a treadmill.');

        // ── Health: Pregnancy & body ─────────────────────────────────
        $add('pregnancy-due-date-calculator', 'Pregnancy Due Date Calculator', 'health', 'Due date and trimester from LMP or conception date.');
        $add('ovulation-calculator', 'Ovulation Calculator', 'health', 'Ovulation date and 6-day fertile window from LMP.');
        $add('pregnancy-week-by-week-calculator', 'Pregnancy Week-by-Week Calculator', 'health', 'Exact gestational age in weeks and days.');
        $add('ivf-total-cost-calculator', 'IVF Total Cost Calculator', 'health', 'Per-cycle IVF cost and expected cycles to live birth.');
        $add('bmi-calculator', 'BMI Calculator', 'health', 'Body mass index with WHO category and healthy weight range.');
        $add('body-fat-calculator', 'Body Fat Calculator', 'health', 'Tape-method body fat percentage with category bands.');
        $add('ideal-weight-calculator', 'Ideal Weight Calculator', 'health', 'Devine, Hamwi, Robinson, Miller and BMI healthy range.');
        $add('glp1-lifetime-cost-calculator', 'GLP-1 (Ozempic / Wegovy) Lifetime Cost', 'health', 'Lifetime drug cost with insurance and comorbidity offsets.');
        $add('biological-age-calculator', 'Biological Age Calculator', 'health', 'Biological age from chronological age and lifestyle inputs.');
        $add('smoking-quit-savings-calculator', 'Smoking Quit Savings Calculator', 'health', 'Money saved by quitting over a projection horizon.');
        $add('eldercare-lifetime-cost-calculator', 'Eldercare Lifetime Cost Calculator', 'health', 'Lifetime care cost by level with caregiver opportunity cost.');
        $add('chronic-illness-lifetime-cost-calculator', 'Chronic Illness Lifetime Cost Calculator', 'health', '30-year medical cost projection for T2D / HTN / CKD.');
        $add('meditation-impact-calculator', 'Meditation Impact Calculator', 'health', 'Practice minutes to estimated wellbeing/productivity impact.');
        $add('cgm-non-diabetic-roi-calculator', 'CGM Non-Diabetic ROI Calculator', 'health', 'Behavior-change value vs CGM subscription cost.');
        $add('a1c-calculator', 'A1c Calculator', 'health', 'Convert A1c to estimated average glucose and back.');
        $add('puppy-weight-predictor', 'Puppy Weight Predictor', 'health', 'Adult weight prediction from puppy age, weight and breed class.');
        $add('heart-age-calculator', 'Heart Age Calculator', 'health', 'Heart age from BP, smoking, diabetes and BMI risk factors.');

        // ── Career: Pay & Take-Home ──────────────────────────────────
        $add('tax-calculator', 'Tax Calculator', 'career', 'Multi-country tax owed estimator with bracket breakdown.');
        $add('take-home-pay-calculator', 'Take-Home Pay Calculator', 'career', 'Net pay after tax and deductions from gross salary.');
        $add('salary-to-hourly-calculator', 'Salary to Hourly Calculator', 'career', 'Convert annual salary to hourly, daily and weekly rates.');
        $add('true-hourly-rate-calculator', 'True Hourly Rate Calculator', 'career', 'Real hourly rate after unpaid overtime and commute.');
        $add('stay-at-home-parent-salary-calculator', 'Stay-at-Home Parent Salary 2027', 'career', 'Replacement-cost salary estimate for stay-at-home parenting.');
        $add('remote-salary-adjustment-calculator', 'Remote Salary Adjustment Calculator', 'career', 'Location-based remote salary adjustment from cost of living.');
        $add('employer-stipend-tax-optimization', 'Employer Stipend Tax Optimization', 'career', 'Optimize taxable vs non-taxable stipend structure.');
        $add('salary-raise-calculator', 'Salary Raise Calculator', 'career', 'New salary and take-home impact from a raise percent.');
        $add('bi-weekly-salary-calculator', 'Bi-Weekly Salary Calculator', 'career', 'Convert annual salary to bi-weekly and per-paycheck amounts.');

        foreach ($this->usStates() as [$slugPrefix, $titleState]) {
            $add(
                $slugPrefix.'-paycheck-calculator',
                $titleState.' Paycheck Calculator',
                'career',
                "Estimate take-home pay for {$titleState} with state tax assumptions (stub — editable engine)."
            );
        }

        // ── Freelance ────────────────────────────────────────────────
        $add('freelance-rate-calculator', 'Freelance Rate Calculator', 'career', 'Recommended freelance hourly/day rate from target income and utilization.');
        $add('international-payment-fee-compare', 'International Payment Fee Compare', 'career', 'Compare Wise, PayPal, wire and card fees for cross-border payouts.');
        $add('w2-vs-1099-equivalent-calculator', 'W-2 vs 1099 Equivalent Calculator', 'career', 'Gross 1099 rate needed to match W-2 take-home.');
        $add('api-token-cost-calculator', 'API Token Cost Calculator / LLM', 'developer', 'Estimate LLM/API token spend for your usage volume.');

        // ── Career decisions ─────────────────────────────────────────
        $add('cost-of-living-calculator', 'Cost of Living Calculator', 'career', 'Compare living costs between cities or countries.');
        $add('job-offer-comparison-calculator', 'Job Offer Comparison Calculator', 'career', 'Side-by-side total compensation comparison for two offers.');
        $add('raise-impact-calculator', 'Raise Impact Calculator', 'career', 'Lifetime earnings impact of a raise including compounding.');
        $add('geo-arbitrage-calculator', 'Geo-Arbitrage Calculator', 'career', 'Keep remote salary while moving to a lower-cost city.');
        $add('global-salary-true-equivalence', 'Global Salary True Equivalence', 'career', 'PPP-adjusted salary equivalence across countries.');
        $add('meeting-cost-calculator', 'Meeting Cost Calculator', 'career', 'Fully-loaded cost of a meeting from attendees and duration.');
        $add('deep-work-roi-calculator', 'Deep Work ROI Calculator', 'career', 'Productivity ROI of protected deep-work blocks.');
        $add('coworking-vs-wfh-true-cost', 'Coworking vs WFH True Cost', 'career', 'True monthly cost of coworking vs working from home.');
        $add('salary-negotiation-counter-offer-calculator', 'Salary Negotiation Counter-Offer Calculator', 'career', 'Suggested counter-offer range from market and current offer.');

        // ── Date & Time ──────────────────────────────────────────────
        $add('age-calculator', 'Age Calculator', 'daily-life', 'Exact age in years, months and days from date of birth, plus totals.');
        $add('dog-age-in-human-years-calculator', 'Dog Age in Human Years Calculator', 'daily-life', 'Convert dog age to human years using the 2019 AVMA logarithmic method.');
        $add('days-between-dates-calculator', 'Days Between Dates Calculator', 'daily-life', 'Exact day count, business days, weeks, months and Y/M/D between two dates.');
        $add('days-until-calculator', 'Days Until Calculator', 'daily-life', 'Countdown (or days-since) from today to any target date in days, weeks and Y/M/D.');
        $add('date-add-subtract-calculator', 'Date Add / Subtract Calculator', 'daily-life', 'Add or subtract years, months and days with calendar-aware month clamping.');
        $add('work-hours-calculator', 'Work Hours Calculator', 'daily-life', 'Clock-in/out plus breaks → elapsed time, net hours and overtime.');
        $add('week-number-calculator', 'Week Number Calculator', 'daily-life', 'ISO 8601 and US week number for any date.');
        $add('time-zone-converter', 'Time Zone Converter', 'daily-life', 'Convert date and time between IANA time zones with DST handling.');
        $add('countdown-timer', 'Countdown Timer', 'daily-life', 'Live countdown to a target date and time in days, hours, minutes and seconds.');
        $add('date-difference-calculator', 'Days Between Dates Calculator', 'daily-life', 'Exact day count, business days, weeks, months and Y/M/D between two dates.');
        $add('countdown-calculator', 'Countdown Timer', 'daily-life', 'Live countdown to a target date and time in days, hours, minutes and seconds.');

        // ── AI Costs, Infra & ROI ────────────────────────────────────
        $add('token-context-window-calculator', 'Token Context Window Calculator', 'developer', 'See what % of a model context window your prompt uses by character count.');
        $add('ai-image-generation-cost-calculator', 'AI Image Generation Cost Calculator', 'developer', 'Monthly image-gen cost by volume, quality, resolution and provider.');
        $add('ai-agent-run-cost-calculator', 'AI Agent Run Cost Calculator', 'developer', 'Agent economics: turns, tokens, retries and tool-call costs per task.');
        $add('fine-tune-vs-rag-calculator', 'Fine-tune vs RAG Calculator', 'developer', 'Compare fine-tuning vs RAG total cost for your corpus and query volume.');
        $add('gpu-rental-vs-api-calculator', 'GPU Rental vs API Calculator', 'developer', 'Open-source LLM on rented GPUs vs hosted API total cost.');
        $add('self-host-vs-api-calculator', 'Self-Host vs API Calculator', 'developer', 'Capex vs opex for production LLM workloads (self-host vs API).');
        $add('ai-job-replacement-risk-calculator', 'AI Job Replacement Risk Calculator', 'developer', 'Role, routine-task share and horizon → AI replacement risk score.');
        $add('personal-ai-stack-roi-calculator', 'Personal AI Stack ROI Calculator', 'developer', 'Sum ChatGPT/Claude/Cursor and niche AI subscriptions vs value.');
        $add('ai-replacement-risk-score-2027', 'AI Replacement Risk Score (2027)', 'developer', '0–100 replacement-risk score over 3/5/10-year horizons.');
        $add('ai-model-cost-calculator', 'AI Model Cost Calculator', 'developer', 'Full LLM TCO beyond the API line item (caching, batch, overhead).');
        $add('ai-tool-stack-roi-calculator', 'AI Tool Stack ROI Calculator', 'developer', 'Net monthly $ saved across AI tool subscriptions, tax-adjusted.');
        $add('openai-token-calculator', 'API Token Cost Calculator (LLM)', 'developer', 'Estimate LLM API spend across providers with caching and batch discounts.');
        $add('api-token-cost-calculator', 'API Token Cost Calculator (LLM)', 'developer', 'Estimate LLM API spend across providers with caching and batch discounts.');

        // ── Everyday Math ────────────────────────────────────────────
        $add('percentage-calculator', 'Percentage Calculator', 'basic-math', 'X% of Y, X is what % of Y, increase/decrease by %, and % change — with full working.');
        $add('fraction-calculator', 'Fraction Calculator', 'basic-math', 'Add, subtract, multiply or divide fractions; simplest, mixed and decimal forms.');
        $add('discount-calculator', 'Discount Calculator', 'basic-math', 'Sale price, savings and reverse discount % from original + sale prices.');
        $add('tip-calculator', 'Tip Calculator', 'basic-math', 'Tip, optional tax and split across diners — per-person total in one tap.');
        $add('vat-sales-tax-calculator', 'VAT / Sales Tax Calculator', 'basic-math', 'Tax-included total or reverse pre-tax price with 20+ country presets.');
        $add('vat-calculator', 'VAT / Sales Tax Calculator', 'basic-math', 'Tax-included total or reverse pre-tax price with country rate presets.');
        $add('sales-tax-calculator', 'VAT / Sales Tax Calculator', 'basic-math', 'Tax-included total or reverse pre-tax price with country rate presets.');
        $add('ratio-calculator', 'Ratio Calculator', 'basic-math', 'Simplify A:B, find equivalents, or split a total — GCD step-by-step.');
        $add('average-calculator', 'Average / Mean Calculator', 'basic-math', 'Mean, median, mode, range, min/max and population SD for up to 10,000 values.');
        $add('average-mean-calculator', 'Average / Mean Calculator', 'basic-math', 'Mean, median, mode, range, min/max and population SD for up to 10,000 values.');
        $add('area-calculator', 'Area Calculator', 'basic-math', 'Area (and perimeter/circumference) for eight common shapes with step-by-step working.');
        $add('volume-calculator', 'Volume Calculator', 'basic-math', 'Volume (and surface area) for eight common 3D shapes with step-by-step working.');
        $add('aspect-ratio-calculator', 'Aspect Ratio Calculator', 'basic-math', 'Solve missing dimension, scale to a target ratio, snap to 16:9 and other standards.');

        // ── School & Grading ─────────────────────────────────────────
        $add('word-counter', 'Word Counter', 'education', 'Words, characters, sentences, paragraphs, avg word length and reading/speaking time.');
        $add('gpa-calculator', 'GPA Calculator', 'education', 'Semester or cumulative GPA on US 4.0 and Indian/Korean 4.5 scales.');
        $add('grade-calculator', 'Grade Calculator', 'education', 'Score needed on the final, or overall course grade and US letter.');
        $add('final-exam-grade-calculator', 'Final Exam Grade Calculator', 'education', 'Live: what score you need on the final for a target letter grade.');

        // ── Conversions ──────────────────────────────────────────────
        $add('unit-converter', 'Unit Converter', 'unit-conversion', 'Length, weight, volume, temperature, area and time in one converter.');
        $add('shoe-size-converter', 'Shoe Size Converter', 'unit-conversion', 'US Men/Women, UK, EU, JP/Mondopoint and CM with brand-fit notes.');

        // ── Advanced & Notation ──────────────────────────────────────
        $add('random-number-generator', 'Random Number Generator', 'basic-math', 'Cryptographic-grade random integers — range, count and duplicate control.');
        $add('scientific-notation-calculator', 'Scientific Notation Calculator', 'basic-math', 'Standard, scientific and engineering notation with order of magnitude.');
        $add('roman-numeral-converter', 'Roman Numeral Converter', 'basic-math', 'Roman ↔ integer 1–3999 with subtractive notation and glyph breakdown.');
        $add('standard-deviation-calculator', 'Standard Deviation Calculator', 'basic-math', 'Population σ or sample s, variance, mean, range and CV — NIST-style.');
        $add('p-value-calculator', 'P-Value Calculator', 'basic-math', 'Exact p-value for Z, T, Chi-square or F tests with α=0.05/0.01 verdicts.');
        $add('z-score-calculator', 'Z-Score Calculator', 'basic-math', 'Z-score, percentile and tail probability — or reverse from z to raw value.');

        // ── Life decisions: Career Pivot ─────────────────────────────
        $add('should-i-quit-my-job-runway-calculator', 'Should I Quit My Job? Runway Calculator', 'life-decisions', 'Runway months after cushion — GO / TIGHT / DON\'T QUIT YET with next actions.');
        $add('back-to-school-roi-calculator', 'Back-to-School ROI Calculator', 'life-decisions', 'Tuition + lost wages vs salary uplift; payback and ROI vs index-fund benchmark.');
        $add('career-switch-bootcamp-roi-calculator', 'Career-Switch Bootcamp ROI Calculator', 'life-decisions', 'Bootcamp ROI with landing-rate risk — expected and conditional returns.');
        $add('phd-vs-work-and-invest-calculator', 'PhD vs Work-and-Invest Calculator', 'life-decisions', 'Year-by-year wealth: PhD stipend path vs work-and-invest compounding.');
        $add('sabbatical-impact-calculator', 'Sabbatical Impact Calculator', 'life-decisions', 'Cash cost plus lifetime opportunity cost of a mid-career sabbatical.');

        // ── Life decisions: Family & Time ────────────────────────────
        $add('divorce-true-cost-calculator', 'Divorce True Cost Calculator', 'life-decisions', 'Lawyer, asset split, alimony NPV and 10-year stay vs divorce counterfactual.');
        $add('should-i-have-a-kid-calculator', 'Should I Have a Kid? Calculator', 'life-decisions', 'Raising-a-child cost + leave loss + retirement impact by region and childcare.');
        $add('ivf-decision-cost-calculator', 'IVF Decision Cost Calculator', 'life-decisions', 'Expected IVF cost and success probability by age and planned cycles.');
        $add('adult-boomerang-kid-cost-calculator', 'Adult Boomerang Kid Cost', 'life-decisions', 'Direct cost, bedroom opportunity cost and retirement compound impact.');
        $add('sandwich-generation-burden-calculator', 'Sandwich Generation Burden', 'life-decisions', 'Cash-flow, retirement sacrifice and burnout risk caring for kids + parents.');

        // ── Life decisions: Relocation ───────────────────────────────
        $add('should-i-move-country-calculator', 'Should I Move Country Calculator', 'life-decisions', 'COL, tax, healthcare and move-cost delta across your stay horizon.');
        $add('apartment-lease-break-cost-calculator', 'Apartment Lease Break Cost', 'life-decisions', 'Break-lease cost across protected walk, sublet, ride-out and buyout paths.');
        $add('moving-cost-calculator', 'Moving Cost Calculator', 'life-decisions', 'DIY, hybrid, full-service and container move estimates with peak-season rates.');

        // ── Life decisions: Long-Game Frames ─────────────────────────
        $add('time-wealth-calculator', 'Time-Wealth Calculator', 'life-decisions', 'Price purchases in true life-hours including unpaid overhead and buy-back arbitrage.');
        $add('funeral-pre-plan-cost-calculator', 'Funeral Pre-Plan Cost Calculator', 'life-decisions', 'Cost band, pre-need vs at-need savings and life-insurance gap.');
        $add('solo-living-premium-calculator', 'Solo-Living Premium', 'life-decisions', 'Monthly solo vs roommate premium and income threshold for sustainability.');
        $add('ai-tutor-vs-human-tutor-calculator', 'AI Tutor vs Human Tutor Calculator (Kids)', 'life-decisions', '12-month AI vs human vs hybrid cost with fit and oversight hours.');
        $add('therapy-roi-calculator', 'Therapy ROI Calculator', 'life-decisions', 'Productivity and relationship value of therapy vs after-insurance cost.');
        $add('lifestyle-inflation-trap-calculator', 'Lifestyle Inflation Trap Calculator', 'life-decisions', 'Years stolen from FI when raises lift spending vs a zero-inflation path.');
        $add('regret-minimization-calculator', 'Regret Minimization Calculator', 'life-decisions', 'EV plus regret-weighted verdict for try-vs-skip life decisions.');

        // ── Retirement: Claiming & Benefits ──────────────────────────
        $add('social-security-claiming-age-optimizer', 'Social Security Claiming Age Optimizer', 'retirement', 'Lifetime NPV at 62 vs FRA vs 70 with COLA and breakeven analysis.');
        $add('social-security-break-even-calculator', 'Social Security Claiming Age Optimizer', 'retirement', 'Lifetime NPV at 62 vs FRA vs 70 with COLA and breakeven analysis.');

        // ── Retirement: Withdrawals & Taxes ──────────────────────────
        $add('rmd-required-minimum-distribution-calculator', 'RMD (Required Minimum Distribution) Calculator', 'retirement', 'Annual RMD from IRS Uniform Lifetime Table, 10-year projection and penalty exposure.');
        $add('roth-conversion-ladder-calculator', 'Roth Conversion Ladder Calculator', 'retirement', 'Optimal yearly conversion to fill your bracket, lifetime tax saved and RMD reduction.');
        $add('annuity-calculator', 'Annuity Calculator', 'retirement', 'Solve FV, PV, payment or payment-to-target — standard actuarial math.');
        $add('hsa-calculator', 'HSA Calculator', 'retirement', 'Lifetime HSA tax advantage vs 401(k)-only and taxable brokerage paths.');
        $add('powerball-annuity-calculator', 'Powerball Annuity Calculator', 'retirement', 'Lump-sum cash vs 30-year annuity net of federal and state lottery tax.');
        $add('401k-early-withdrawal-calculator', '401(k) Early Withdrawal Calculator', 'retirement', '10% penalty + tax + opportunity cost, with SECURE Act 2.0 exception flags.');

        // ── Retirement: Pension & Medicare ───────────────────────────
        $add('medicare-part-d-vs-advantage-compare', 'Medicare Part D vs Advantage Compare', 'retirement', 'All-in annual cost: Original + Part D vs Advantage with network score.');
        $add('pension-lump-sum-vs-monthly-decision-calculator', 'Pension Lump-Sum vs Monthly Decision Calculator', 'retirement', 'NPV both paths, breakeven age and longevity sensitivity at LE 75/85/95.');
        $add('pension-lump-sum-vs-annuity-calculator', 'Pension Lump-Sum vs Monthly Decision Calculator', 'retirement', 'NPV both paths, breakeven age and longevity sensitivity.');

        // ── Point-to-point Conversions ───────────────────────────────
        $add('kg-to-lbs-converter', 'Kg to Lbs Converter', 'unit-conversion', 'Convert kilograms to pounds with the exact 2.20462 factor and formula.');
        $add('lbs-to-kg-converter', 'Lbs to Kg Converter', 'unit-conversion', 'Convert pounds to kilograms with the exact factor and formula.');
        $add('grams-to-ounces-converter', 'Grams to Ounces Converter', 'unit-conversion', 'Convert grams to ounces with the exact factor and common-values table.');
        $add('cm-to-inches-converter', 'Cm to Inches Converter', 'unit-conversion', 'Convert centimeters to inches with the exact factor and formula.');
        $add('inches-to-cm-converter', 'Inches to Cm Converter', 'unit-conversion', 'Convert inches to centimeters with the exact factor and formula.');
        $add('meters-to-feet-converter', 'Meters to Feet Converter', 'unit-conversion', 'Convert meters to feet with the exact factor and formula.');
        $add('miles-to-km-converter', 'Miles to Km Converter', 'unit-conversion', 'Convert miles to kilometers with the exact factor and formula.');
        $add('mm-to-inches-converter', 'Mm to Inches Converter', 'unit-conversion', 'Convert millimeters to inches with the exact factor and formula.');
        $add('km-to-miles-converter', 'Km to Miles Converter', 'unit-conversion', 'Convert kilometers to miles with the exact factor and formula.');
        $add('feet-to-meters-converter', 'Feet to Meters Converter', 'unit-conversion', 'Convert feet to meters with the exact factor and formula.');
        $add('oz-to-ml-converter', 'Oz to Ml Converter', 'unit-conversion', 'Convert fluid ounces to milliliters with the exact factor and formula.');
        $add('cups-to-ounces-converter', 'Cups to Ounces Converter', 'unit-conversion', 'Convert cups to fluid ounces with the exact factor and formula.');
        $add('ml-to-cups-converter', 'Ml to Cups Converter', 'unit-conversion', 'Convert milliliters to cups with the exact factor and formula.');
        $add('liters-to-gallons-converter', 'Liters to Gallons Converter', 'unit-conversion', 'Convert liters to gallons with the exact factor and formula.');
        $add('celsius-to-fahrenheit-converter', 'Celsius to Fahrenheit Converter', 'unit-conversion', 'Convert Celsius to Fahrenheit with the exact formula shown.');
        $add('fahrenheit-to-celsius-converter', 'Fahrenheit to Celsius Converter', 'unit-conversion', 'Convert Fahrenheit to Celsius with the exact formula shown.');

        // ── Home improvement ─────────────────────────────────────────
        $add('concrete-calculator', 'Concrete Calculator', 'construction', 'Cubic yards, premix bag count and ready-mix truck cost for slabs, footings, columns, stairs and walls.');
        $add('roofing-calculator', 'Roofing Calculator', 'construction', 'Roofing squares, shingle bundles, ridge and drip edge — material + labor across tiers.');
        $add('paint-calculator', 'Paint Calculator', 'home', 'Gallons for walls, ceiling and trim with DIY vs pro cost and coverage tables.');
        $add('drywall-calculator', 'Drywall Calculator', 'construction', 'Sheet count, mud, tape and screws — DIY material and pro hang/finish labor.');
        $add('lumber-calculator', 'Lumber Calculator', 'construction', 'Board feet, linear feet and cost range from a cut list and species.');
        $add('deck-cost-calculator', 'Deck Cost Calculator', 'home', 'Installed deck cost, $/sq-ft, lifetime cost/year and resale value added.');
        $add('bathroom-renovation-cost-calculator', 'Bathroom Renovation Cost Calculator', 'home', 'Renovation cost range by scope, ROI and timeline comparison.');
        $add('fence-calculator', 'Fence Calculator', 'home', 'Posts, concrete, pickets and project cost across fence materials.');

        return $items;
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    protected function usStates(): array
    {
        return [
            ['california', 'California'],
            ['texas', 'Texas'],
            ['new-york', 'New York'],
            ['florida', 'Florida'],
            ['pennsylvania', 'Pennsylvania'],
            ['illinois', 'Illinois'],
            ['ohio', 'Ohio'],
            ['georgia', 'Georgia'],
            ['north-carolina', 'North Carolina'],
            ['michigan', 'Michigan'],
            ['new-jersey', 'New Jersey'],
            ['virginia', 'Virginia'],
            ['washington', 'Washington'],
            ['arizona', 'Arizona'],
            ['massachusetts', 'Massachusetts'],
            ['tennessee', 'Tennessee'],
            ['indiana', 'Indiana'],
            ['missouri', 'Missouri'],
            ['maryland', 'Maryland'],
            ['wisconsin', 'Wisconsin'],
            ['colorado', 'Colorado'],
            ['minnesota', 'Minnesota'],
            ['south-carolina', 'South Carolina'],
            ['alabama', 'Alabama'],
            ['louisiana', 'Louisiana'],
            ['kentucky', 'Kentucky'],
            ['oregon', 'Oregon'],
            ['oklahoma', 'Oklahoma'],
            ['connecticut', 'Connecticut'],
            ['utah', 'Utah'],
            ['iowa', 'Iowa'],
            ['nevada', 'Nevada'],
            ['arkansas', 'Arkansas'],
            ['mississippi', 'Mississippi'],
            ['kansas', 'Kansas'],
            ['new-mexico', 'New Mexico'],
            ['nebraska', 'Nebraska'],
            ['west-virginia', 'West Virginia'],
            ['idaho', 'Idaho'],
            ['hawaii', 'Hawaii'],
            ['new-hampshire', 'New Hampshire'],
            ['maine', 'Maine'],
            ['montana', 'Montana'],
            ['rhode-island', 'Rhode Island'],
            ['delaware', 'Delaware'],
            ['south-dakota', 'South Dakota'],
            ['north-dakota', 'North Dakota'],
            ['alaska', 'Alaska'],
            ['vermont', 'Vermont'],
            ['wyoming', 'Wyoming'],
            ['district-of-columbia', 'District of Columbia'],
        ];
    }
}
