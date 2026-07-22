<?php

namespace App\Services\Seo;

use App\Models\Calculator;
use Illuminate\Support\Str;

/**
 * Builds 1000–1400 word SEO how-to articles for top calculators.
 * Word count is measured on stripped HTML text.
 */
class TopCalculatorBlogContentBuilder
{
    /**
     * @return array{
     *   title: string,
     *   slug: string,
     *   excerpt: string,
     *   content: string,
     *   meta_title: string,
     *   meta_description: string,
     *   meta_keywords: string,
     *   reading_time_minutes: int,
     *   category: string,
     *   tags: list<string>,
     *   calculators: list<string>,
     *   word_count: int
     * }
     */
    public function build(Calculator $calculator, int $dayIndex): array
    {
        $title = $calculator->title;
        $slug = 'how-to-use-'.$calculator->slug;
        $topic = Str::lower($title);
        $fields = $this->fieldLabels($calculator);
        $fieldList = $fields !== [] ? $this->oxford($fields) : 'your values';
        $catSlug = $calculator->category?->slug ?? 'how-to-calculators';
        $blogCategory = $this->mapBlogCategory($catSlug);
        $tags = $this->mapTags($catSlug);

        $body = $this->composeHtml($calculator, $title, $topic, $fieldList, $fields);
        $wordCount = $this->wordCount($body);

        // Ensure 1000–1400 words of real prose (not HTML chrome).
        $guard = 0;
        while ($wordCount < 1000 && $guard < 8) {
            $body .= $this->extraDepthSection($calculator, $title, $topic, $fieldList, $guard);
            $wordCount = $this->wordCount($body);
            $guard++;
        }

        if ($wordCount > 1400) {
            $body = $this->trimToWordBudget($body, 1380);
            $wordCount = $this->wordCount($body);
        }

        // If trim overshot below floor, add one short depth section back.
        $guard = 0;
        while ($wordCount < 1000 && $guard < 5) {
            $body .= $this->extraDepthSection($calculator, $title, $topic, $fieldList, $guard + 3);
            $wordCount = $this->wordCount($body);
            $guard++;
        }

        if ($wordCount > 1400) {
            $body = $this->trimToWordBudget($body, 1380);
            $wordCount = $this->wordCount($body);
        }

        $excerpt = "A practical {$wordCount}+ word guide to the {$title}: inputs, formulas, worked examples, common mistakes, and how to get reliable results on AI Calculator Hub.";
        if (strlen($excerpt) > 280) {
            $excerpt = Str::limit($excerpt, 277);
        }

        return [
            'title' => "How to Use the {$title} (Complete Guide with Examples)",
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $body,
            'meta_title' => Str::limit("{$title} Guide: How to Calculate Accurately | Calculator Hub", 60, ''),
            'meta_description' => Str::limit("Learn how to use the free {$title}. Enter {$fieldList}, follow worked examples, avoid common mistakes, and interpret results with confidence.", 155, ''),
            'meta_keywords' => implode(', ', array_unique(array_filter([
                Str::lower($title),
                'how to use '.$topic,
                $topic.' guide',
                'free calculator',
                'worked example',
                $calculator->category?->name,
            ]))),
            'reading_time_minutes' => max(5, (int) ceil($wordCount / 200)),
            'category' => $blogCategory,
            'tags' => $tags,
            'calculators' => [$calculator->slug],
            'word_count' => $wordCount,
            'is_featured' => $dayIndex === 0,
        ];
    }

    public function wordCount(string $html): int
    {
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';
        if ($text === '') {
            return 0;
        }

        preg_match_all('/[\p{L}\p{N}\']+/u', $text, $matches);

        return count($matches[0]);
    }

    /**
     * @param  list<string>  $fields
     */
    protected function composeHtml(Calculator $c, string $title, string $topic, string $fieldList, array $fields): string
    {
        $lead = $this->lead($c, $title, $topic);
        $why = $this->whyItMatters($c, $title, $topic);
        $how = $this->howItWorks($c, $title, $topic, $fieldList);
        $steps = $this->steps($c, $title, $fieldList, $fields);
        $examples = $this->examples($c, $title, $topic);
        $mistakes = $this->mistakes($c, $title, $topic);
        $tips = $this->tips($c, $title, $topic, $fieldList);
        $interpret = $this->interpret($c, $title, $topic);
        $faq = $this->faqBlock($c, $title);
        $closing = $this->closing($c, $title, $topic);

        return <<<HTML
{$lead}
{$why}
{$how}
{$steps}
{$examples}
{$mistakes}
{$tips}
{$interpret}
{$faq}
{$closing}
HTML;
    }

    protected function lead(Calculator $c, string $title, string $topic): string
    {
        $custom = $this->customOpeners()[$c->slug] ?? null;
        if ($custom) {
            return $custom;
        }

        $short = trim((string) ($c->short_description ?: $c->description));
        $short = $short !== '' ? $short : "quick, transparent {$topic} results";

        return <<<HTML
<p>If you have ever stalled over a spreadsheet, second-guessed a formula, or accepted a rough guess because the math felt messy, the <strong>{$title}</strong> is built for you. This long-form guide explains what the tool measures, which inputs matter, how the underlying approach works, and how to turn the output into a decision you can defend. Along the way you will see worked examples, edge cases, and the mistakes that quietly ruin otherwise careful estimates.</p>
<p>People open a {$topic} tool for different reasons: budgeting, planning, studying, compliance, or simply curiosity. Whatever brought you here, the goal is the same—replace vague intuition with a number you understand. The short promise of the calculator is simple: {$short} The longer promise of this article is that you will know <em>why</em> that number looks the way it does, and when you should slow down before acting on it.</p>
<p>AI Calculator Hub publishes free calculators with transparent inputs and readable breakdowns. Use this guide as a field manual: skim the sections you need, or read it end to end before you rely on the result for money, health, construction, tax, or academic decisions.</p>
HTML;
    }

    /**
     * @return array<string, string>
     */
    protected function customOpeners(): array
    {
        return [
            'nepal-income-tax-calculator' => <<<'HTML'
<p>Nepal’s personal income-tax rules look straightforward on a slab chart and surprisingly easy to mis-apply once you mix monthly versus annual figures, Social Security Fund (SSF) treatment, and the difference between taxable income and take-home pay. The <strong>Nepal Income Tax Calculator</strong> exists so you can model FY-style progressive bands without rebuilding the brackets in a spreadsheet every time the fiscal year or your payroll assumptions change.</p>
<p>This guide walks through how to enter income by period, when the SSF / approved retirement-fund toggle matters, how to read the tax due versus effective rate, and how to sanity-check results against a sample payslip. You will also see common payroll mistakes—like treating gross monthly salary as annual taxable income without multiplying by twelve—and how those mistakes inflate or understate liability.</p>
<p>Whether you are an employee comparing job offers, a freelancer estimating quarterly cash needs, or a student learning progressive taxation, treat the calculator as a planning lens first. Final filings still belong with IRD guidance and a qualified tax professional when your situation includes capital gains, business income, or complex exemptions.</p>
HTML,
            'age-calculator' => <<<'HTML'
<p>Age sounds like the simplest number you can ask for, yet calendar math is full of traps: leap days, month-length differences, and the question of whether you want age “as of today,” on a legal deadline, or on a future event. The <strong>Age Calculator</strong> converts a date of birth and an optional reference date into years, months, and days—plus helpful totals—so you stop counting on your fingers or debating offline date libraries.</p>
<p>This article shows how to use birth date and as-of date fields correctly, how next-birthday countdowns are derived, and when age-in-days matters for eligibility rules, school admissions, HR forms, or personal milestones. You will also learn why two tools can disagree by a day and how to document the reference date you used.</p>
<p>Read this as a practical how-to: short enough to skim before filling a form, detailed enough to teach the underlying calendar difference approach without requiring programming experience.</p>
HTML,
            'bmi-calculator' => <<<'HTML'
<p>Body Mass Index remains one of the most widely used screening metrics in clinics, workplace wellness programs, and personal health tracking—even though it was never designed as a complete portrait of fitness. The <strong>BMI Calculator</strong> applies the standard weight ÷ height² formula (with optional imperial conversion) and maps the result to commonly cited WHO-style categories and a healthy weight range.</p>
<p>In this guide you will learn how to choose metric or imperial units, how to interpret categories without over-reading them, and why athletes, older adults, and people with higher muscle mass may need additional measures such as waist circumference or clinical assessment. Worked examples show how small height errors swing BMI more than many people expect.</p>
<p>Use the calculator as a fast screening companion, not a diagnosis. Pair the number with context, and talk to a healthcare professional before making medical decisions from a single index.</p>
HTML,
            'sip-calculator' => <<<'HTML'
<p>A Systematic Investment Plan (SIP) turns investing into a monthly habit: the same contribution, compounding over time, with market returns doing the heavy lifting in the later years. The <strong>SIP Calculator</strong> projects future value from your monthly amount, expected annual return, and tenure so you can compare goals before you commit money.</p>
<p>This guide explains the future-value-of-annuity logic behind SIP estimates, how to pick a return assumption without fantasy numbers, and how to stress-test tenure versus contribution size. You will see examples for short goals (three to five years) and long goals (fifteen-plus years), plus the behavioral mistakes that matter more than decimal precision—skipping months, raising lifestyle with every raise, or treating a projection as a guarantee.</p>
<p>Markets vary. The calculator is an educational planner. Confirm product-level fees, taxes, and risk with your advisor or fund documents before investing.</p>
HTML,
            'driving-license-fee-calculator' => <<<'HTML'
<p>Transport office fees in Nepal are easy to underestimate when you forget categories, trial fees, or renewals versus first-time applications. The <strong>Driving License Fee Calculator</strong> helps you total the likely cost path so you arrive prepared instead of discovering gaps at the counter.</p>
<p>This article covers which inputs to gather first (license category, new versus renewal context, and any extras your local office publishes), how to read the fee breakdown, and how to budget buffer for photos, medical certificates, or re-test attempts. Worked scenarios compare a first motorcycle license path with a renewal case so you can see where totals diverge.</p>
<p>Official fee schedules can change. Always cross-check the latest Department of Transport Management notices for your office; use this calculator as a planning estimate, not a receipt.</p>
HTML,
            'gpa-calculator' => <<<'HTML'
<p>Grade Point Average is the language of transcripts, scholarships, and semester reviews—yet students still mix letter grades, credit hours, and scale conversions. The <strong>GPA Calculator</strong> weights each course’s grade points by credit hours so your semester GPA reflects academic load, not just the average of letter marks.</p>
<p>Here you will learn how to enter courses consistently, when to use letter versus numeric grade points, how incomplete or repeated courses can distort a manual average, and how to interpret GPA movement after a heavy credit semester. Examples show a light elective term versus a lab-heavy term so the credit-weighting effect is obvious.</p>
<p>Institution policies differ. Treat the calculator as a planning mirror of your transcript rules, then confirm with your registrar’s scale if scholarships or honors thresholds are involved.</p>
HTML,
            'aana-sqm-converter' => <<<'HTML'
<p>In Nepal, land conversations still move fluently between traditional units like aana and modern metric measures like square metres. Mis-converting even a small area can scramble a purchase discussion or a municipal form. The <strong>Aana Sqm Converter</strong> translates between these systems quickly so both sides of a deal share the same geometric meaning.</p>
<p>This guide explains typical conversion relationships used in everyday Nepal property discussions, how to verify direction (aana→sqm versus sqm→aana), and why deed documents, broker quotes, and survey sketches sometimes disagree. You will also find practical checks before you sign—rounding rules, plot irregularity, and the difference between area claims and surveyed area.</p>
<p>Land transactions deserve professional survey and legal review. Use the converter for clarity in conversation and paperwork drafts, then confirm with licensed survey and title professionals.</p>
HTML,
            'brick-calculator' => <<<'HTML'
<p>Brick orders fail in two expensive ways: shortfalls that stall masonry crews, and overbuys that freeze cash in unused pallets. The <strong>Brick Calculator</strong> estimates brick count and mortar needs from wall dimensions, brick size, joint thickness, and wastage—so your materials list matches the wall you are actually building.</p>
<p>This article walks through measuring net wall area after openings, converting to volume, applying effective brick size with mortar joints, and choosing a realistic wastage percentage for straight walls versus cut-heavy work. Worked examples show how a door opening and a thicker joint each change totals.</p>
<p>Site conditions vary by region and brick standard. Validate dimensions with your supplier’s actual brick size before final procurement.</p>
HTML,
            'emi-calculator' => <<<'HTML'
<p>Equated Monthly Installments turn a large loan into a predictable monthly payment—but the reducing-balance formula behind EMI is opaque if you only look at marketing “sample” tables. The <strong>EMI Calculator</strong> computes payment, total interest, and total outflow from principal, annual rate, and tenure so you can compare offers on equal footing.</p>
<p>In this guide you will see how monthly rate and tenure interact, why longer tenures lower EMI but raise lifetime interest, and how to use the breakdown when negotiating prepayment or down payment. Examples contrast a shorter high-EMI plan with a longer low-EMI plan on the same principal.</p>
<p>Loan contracts include fees and penalties the calculator may not encode. Use it for comparison math, then read the sanction letter carefully.</p>
HTML,
            'ac-size-calculator' => <<<'HTML'
<p>An oversized air conditioner short-cycles and wastes money; an undersized unit runs endlessly and never comforts the room. The <strong>AC Sizing + Cost Calculator</strong> approximates capacity using a Manual J–style square-foot method adjusted for climate, sun, occupancy, and insulation—then compares efficiency tiers on multi-year operating cost.</p>
<p>This guide shows which room inputs move tonnage the most, how to read BTU/hr versus ton rounding, and why SEER/SEER2 differences show up in the ten-year cost view. You will also learn the limits of rule-of-thumb sizing for unusual rooms, kitchens, or very leaky envelopes.</p>
<p>Complex homes deserve a full Manual J by an HVAC professional. Use this calculator to shortlist sizes and efficiency tiers before you request quotes.</p>
HTML,
            'api-cost-calculator' => <<<'HTML'
<p>API bills sneak up when token counts, request volume, and unit prices live in separate dashboards. The <strong>Api Cost Calculator</strong> helps you estimate spend from usage assumptions so product and finance teams share one planning number before traffic spikes.</p>
<p>This article covers how to gather unit prices and monthly volumes, how to model growth scenarios, and how to present a cost range instead of a false single-point forecast. Examples show a low-traffic MVP versus a launch month with retries and logging overhead.</p>
<p>Vendor pricing pages change. Re-run the calculator whenever your provider updates rates or you add a new endpoint class.</p>
HTML,
            'compound-interest-calculator' => <<<'HTML'
<p>Compound interest is the quiet engine behind savings accounts, deposits, and long-horizon investing education. The <strong>Compound Interest Calculator</strong> projects how principal grows when returns themselves earn returns across compounding periods.</p>
<p>Here you will learn the difference between nominal rate and compounding frequency, how contributions change the trajectory, and why early years look disappointing while later years accelerate. Worked examples compare annual versus monthly compounding on the same headline rate.</p>
<p>Projections are educational. Inflation, taxes, and fees can shrink real-world outcomes—adjust assumptions conservatively.</p>
HTML,
            'dashain-allowance-calculator' => <<<'HTML'
<p>Dashain allowance planning is a seasonal payroll and household ritual in Nepal: employers estimate obligations, and families budget festival spending. The <strong>Dashain Allowance Calculator</strong> helps turn salary inputs into an allowance figure aligned with common workplace practices so fewer surprises hit in festival month.</p>
<p>This guide explains which salary components people usually include, how to document company policy versus informal norms, and how to communicate the result to employees clearly. Examples compare a basic-salary-only approach with a broader earnings base.</p>
<p>Company policies and labor guidance prevail. Use the calculator to draft numbers, then confirm against your HR policy handbook.</p>
HTML,
            'land-measurement-nepal-calculator' => <<<'HTML'
<p>Nepal land measurement spans traditional units and metric conversions that show up in sale deeds, inheritance discussions, and municipal files. The <strong>Land Measurement Nepal Calculator</strong> helps you move between those languages without relying on contested mental math.</p>
<p>This article covers gathering source measurements carefully, converting with consistent unit direction, and validating results against survey sketches. You will also see why irregular plots and road setbacks make “simple area” claims incomplete.</p>
<p>Always finalize land matters with licensed surveyors and legal counsel. The calculator supports understanding—not title certification.</p>
HTML,
            'aggregate-calculator' => <<<'HTML'
<p>Aggregates—sand, gravel, crushed stone—drive concrete and base-course costs. The <strong>Aggregate Calculator</strong> estimates quantities from geometry and mix assumptions so procurement matches the pour or compaction plan.</p>
<p>In this guide you will learn which dimensions to measure twice, how wastage and bulking assumptions change orders, and how to communicate cubic metres versus truck loads with suppliers. Worked examples show a slab pour versus a pathway base.</p>
<p>Supplier grading and moisture affect real yields. Confirm with site tests and vendor delivery norms.</p>
HTML,
            'salary-tax-calculator' => <<<'HTML'
<p>Salary tax estimates sit at the intersection of gross pay, deductions, and progressive brackets. The <strong>Salary Tax Calculator</strong> helps you explore how taxable income maps to liability under a selected table so offers and payroll changes are easier to compare.</p>
<p>This guide explains period selection, deduction handling, and how to read effective versus marginal rates. Examples show how a raise can land in a higher band without meaning every dollar is taxed at the top rate.</p>
<p>Tax law is jurisdiction-specific. Use the tool for education and planning, then verify with local rules or a tax professional.</p>
HTML,
            'weight-converter' => <<<'HTML'
<p>Weight conversion errors show up in shipping labels, gym programs, recipes, and lab notes—often from mixing pounds, kilograms, and ounces in one workflow. The <strong>Weight Converter</strong> performs clean unit translation so teams share one number.</p>
<p>This article covers common unit pairs, precision and rounding, and how to avoid double-converting through an intermediate unit. Examples include parcel shipping and strength-training plate math.</p>
<p>When regulated trade or medical dosing is involved, follow statutory conversion tables and professional standards.</p>
HTML,
            'carpet-area-calculator' => <<<'HTML'
<p>Carpet area drives flooring budgets and, in many markets, how buyers compare apartments. The <strong>Carpet Area Calculator</strong> helps estimate usable floor area from room dimensions so material orders and comparisons stay grounded.</p>
<p>This guide shows how to measure rooms, handle irregular shapes, and separate wall thickness conversations from usable area. Worked examples cover a simple rectangle set and a layout with a corridor.</p>
<p>Developer brochures may use different area definitions (carpet, built-up, super built-up). Align terminology before you negotiate.</p>
HTML,
            'gratuity-calculator' => <<<'HTML'
<p>Gratuity estimates matter at resignation, retirement, and HR planning conversations. The <strong>Gratuity Calculator</strong> helps model benefit amounts from wage and service inputs aligned with common statutory patterns used in Nepal-focused tooling on the hub.</p>
<p>This article explains which wage components to confirm with HR, how service length enters the estimate, and how to document assumptions when comparing offers. Examples show mid-career versus long-service cases.</p>
<p>Statutory formulas and eligibility rules can change. Verify against current labor law and your employment contract.</p>
HTML,
            'calorie-calculator' => <<<'HTML'
<p>Calorie targets are only useful when they reflect body size, activity, and goal direction. The <strong>Calorie Calculator</strong> estimates needs from common metabolic approaches so meal planning starts from a reasoned baseline instead of a viral round number.</p>
<p>This guide covers activity multipliers, goal adjustments, and why weekly averages beat daily perfectionism. Examples compare sedentary and active profiles on the same body metrics.</p>
<p>Individual medical needs differ. Seek professional guidance for clinical nutrition, eating disorders, or athletic periodization.</p>
HTML,
        ];
    }

    protected function whyItMatters(Calculator $c, string $title, string $topic): string
    {
        $cat = $c->category?->name ?? 'everyday calculation';

        return <<<HTML
<h2>Why a dedicated {$title} beats ad-hoc math</h2>
<p>Ad-hoc math fails for predictable reasons: mixed units, forgotten edge cases, and silent rounding. A dedicated {$topic} workflow reduces those failure modes by forcing you to name each input, apply a consistent method, and review an explicit output. That matters in {$cat}, where small input mistakes can cascade into money, materials, grades, or compliance headaches.</p>
<p>Another advantage is communication. When you share a result—“the calculator returned X given these inputs”—collaborators can reproduce your path. Spreadsheet tabs named “final_final_v7” rarely offer the same audit trail. For students and professionals alike, reproducibility is part of trust.</p>
<p>Finally, calculators encourage scenario thinking. Change one input at a time and watch the result move. That sensitivity analysis is how you learn which assumptions deserve extra care and which barely matter for your decision.</p>
HTML;
    }

    protected function howItWorks(Calculator $c, string $title, string $topic, string $fieldList): string
    {
        $formula = trim((string) ($c->formula_description ?: ''));
        if ($formula === '') {
            $formula = "The {$title} applies a standard {$topic} method using {$fieldList}, then presents the primary result with any available breakdown.";
        }
        $desc = trim((string) ($c->description ?: $c->short_description));
        if ($desc === '') {
            $desc = "It is designed to produce clear {$topic} results you can inspect and adjust.";
        }

        return <<<HTML
<h2>How the {$title} works</h2>
<p>{$desc}</p>
<p><strong>Method in plain language:</strong> {$formula}</p>
<p>In practice, you will enter {$fieldList}. The interface validates obvious empties and non-numeric values where relevant, then computes the result set. Some tools also show intermediate lines—rates, counts, totals, or categories—so you can verify the path, not only the headline number.</p>
<p>Transparency is intentional. If an output looks surprising, walk backward through the breakdown: confirm units, confirm period (monthly versus annual), and confirm that optional toggles match your real-world situation. Most “calculator bugs” reported by users are actually mismatched assumptions.</p>
HTML;
    }

    /**
     * @param  list<string>  $fields
     */
    protected function steps(Calculator $c, string $title, string $fieldList, array $fields): string
    {
        $lis = '';
        if ($fields === []) {
            $lis = '<li>Enter each required value carefully, using consistent units.</li><li>Click Calculate and read the primary result plus breakdown.</li>';
        } else {
            foreach ($fields as $i => $label) {
                $n = $i + 1;
                $lis .= "<li><strong>Step {$n} — {$label}:</strong> Enter a realistic value. If a unit is shown, keep every related field in that unit system.</li>";
            }
            $lis .= '<li><strong>Final step — Calculate:</strong> Submit the form, then screenshot or note the inputs alongside the outputs for your records.</li>';
        }

        return <<<HTML
<h2>Step-by-step: using the {$title}</h2>
<ol>
{$lis}
</ol>
<p>Before you trust a first run, complete one “known answer” check. If you already know a textbook example, payroll stub, or prior project total, enter those inputs and confirm the calculator lands close. That single rehearsal catches unit mistakes faster than rereading help text.</p>
<p>Then run your real case. Change one uncertain input—rate, tenure, wastage, activity level—and observe the swing. If a tiny input change creates a huge output change, invest more effort validating that input from source documents.</p>
<p>Required core fields for this tool typically include {$fieldList}. Optional fields, when present, refine edge cases; leaving them blank usually applies safe defaults rather than zeroing the entire model.</p>
HTML;
    }

    protected function examples(Calculator $c, string $title, string $topic): string
    {
        $slug = $c->slug;

        $extra = match (true) {
            str_contains($slug, 'tax') || str_contains($slug, 'emi') || str_contains($slug, 'sip') || str_contains($slug, 'interest') || str_contains($slug, 'gratuity') || str_contains($slug, 'salary') => <<<HTML
<p><strong>Example A (conservative):</strong> Use round numbers and a mid-range rate or tenure. Record EMI or tax payable, total outflow, and effective rate if shown. Ask: does this payment fit a fixed percentage of take-home pay?</p>
<p><strong>Example B (stress test):</strong> Increase the rate by one percentage point or shorten the tenure. Compare the new payment and total interest. The goal is not prediction perfection—it is knowing your break points before you sign.</p>
<p><strong>Example C (goal reverse):</strong> Start from a payment you can afford and adjust principal or tenure until the calculator’s payment lands near that budget. This reverse workflow prevents lifestyle-inflation borrowing.</p>
HTML,
            str_contains($slug, 'brick') || str_contains($slug, 'aggregate') || str_contains($slug, 'carpet') || str_contains($slug, 'ac-size') || str_contains($slug, 'land') || str_contains($slug, 'aana') => <<<HTML
<p><strong>Example A (simple rectangle):</strong> Measure length and width twice. Enter clean dimensions and a modest wastage or climate factor. Note materials or capacity. Then add openings or sun exposure and watch totals move.</p>
<p><strong>Example B (real site noise):</strong> Include doors, corridors, or roof overhangs as your tool allows. Procurement lists should match the noisier model, not the optimistic sketch.</p>
<p><strong>Example C (supplier check):</strong> Re-run with the supplier’s actual brick size, board width, or tonnage increments. Factory realities beat catalog ideals.</p>
HTML,
            str_contains($slug, 'bmi') || str_contains($slug, 'calorie') || str_contains($slug, 'age') || str_contains($slug, 'gpa') || str_contains($slug, 'weight') => <<<HTML
<p><strong>Example A (baseline):</strong> Enter current measurements carefully—height to the same precision you would use clinically or on a transcript. Save the result.</p>
<p><strong>Example B (what-if):</strong> Change one variable (weight, activity, grades, reference date). Interpret directionally: is the change meaningful or noise?</p>
<p><strong>Example C (second opinion metrics):</strong> Pair the result with another indicator—waist measure, weekly average intake, credit-weighted GPA versus unweighted—to avoid single-metric tunnel vision.</p>
HTML,
            default => <<<HTML
<p><strong>Example A:</strong> Enter a simple, documented baseline for {$topic} and save the outputs.</p>
<p><strong>Example B:</strong> Adjust the most uncertain input by ±10% and compare.</p>
<p><strong>Example C:</strong> Recreate a past known result to validate your unit choices before live use.</p>
HTML,
        };

        return <<<HTML
<h2>Worked examples you can recreate</h2>
<p>Examples cement muscle memory. Recreate each one in the live <strong>{$title}</strong>, then substitute your real numbers. If your recreation diverges wildly from the narrative below, you likely mixed units or periods.</p>
{$extra}
<p>After three runs, write a one-sentence conclusion in your notes: the decision, the key input that drove it, and the residual risk. That sentence is what you bring to a contractor, lender, teacher, or clinician—not a raw screenshot alone.</p>
HTML;
    }

    protected function mistakes(Calculator $c, string $title, string $topic): string
    {
        return <<<HTML
<h2>Common mistakes (and how to avoid them)</h2>
<ul>
<li><strong>Unit mixing:</strong> Kilograms with inches, monthly income treated as annual, or metres with millimetre brick sizes. Align units before emotional interpretation of the result.</li>
<li><strong>False precision:</strong> A result with many decimals is not more true if inputs were rough. Round in a way that matches measurement quality.</li>
<li><strong>Ignoring optional toggles:</strong> SSF flags, unit systems, wastage, or activity multipliers exist because defaults are not universal.</li>
<li><strong>Single-scenario planning:</strong> One happy-path run hides fragility. Always store a pessimistic companion scenario for {$topic} decisions with downside risk.</li>
<li><strong>Skipping documentation:</strong> Without recorded inputs, you cannot explain the number later to a partner, auditor, or future self.</li>
</ul>
<p>If the {$title} offers a reset or sample example, use it when onboarding teammates. Shared samples create a common language for “good input hygiene.”</p>
HTML;
    }

    protected function tips(Calculator $c, string $title, string $topic, string $fieldList): string
    {
        return <<<HTML
<h2>Accuracy tips for better {$topic} results</h2>
<p>Measure twice when geometry or body metrics are involved. Pull rates and fees from primary documents—sanction letters, utility tariffs, IRD notices, syllabi—not memory. For financial tools, separate “headline rate” from fees that effectively change cost.</p>
<p>Keep a tiny input checklist beside the form: {$fieldList}. Tick each line from a source artifact. This ritual is boring and extraordinarily effective.</p>
<p>When results inform purchases or contracts, add a buffer appropriate to the domain: material wastage, contingency budget, or time slack. Calculators estimate; execution still meets friction.</p>
<p>Revisit saved scenarios after major life or project changes. A {$title} result from last year may be directionally useful and numerically stale.</p>
HTML;
    }

    protected function interpret(Calculator $c, string $title, string $topic): string
    {
        return <<<HTML
<h2>How to interpret the output responsibly</h2>
<p>Start with the primary result, then read any breakdown lines. Ask three questions: (1) Do the units match my question? (2) Which input, if wrong, would change the decision? (3) What external constraint—law, safety factor, institutional policy—does the calculator not know?</p>
<p>For borderline results, do not force a narrative onto noise. If BMI sits near a category boundary, or EMI sits near a budget ceiling, collect better inputs or professional advice instead of arguing about the third decimal.</p>
<p>Share results with context. “EMI is X at rate Y for Z years on principal P” is actionable. “The bank is expensive” without numbers is not. The {$title} gives you the former; this guide trains you to speak it.</p>
HTML;
    }

    protected function faqBlock(Calculator $c, string $title): string
    {
        $faqs = $c->faqs ?? collect();
        $items = '';
        if ($faqs->isNotEmpty()) {
            foreach ($faqs->take(6) as $faq) {
                $q = e($faq->question);
                $a = e($faq->answer);
                $items .= "<h3>{$q}</h3><p>{$a}</p>";
            }
        } else {
            $items = <<<HTML
<h3>Is the {$title} free to use?</h3>
<p>Yes. You can run calculations on AI Calculator Hub without payment. A free account helps you save favorites and revisit results.</p>
<h3>How accurate are the results?</h3>
<p>Accuracy tracks the quality of your inputs and the standard method implemented for this tool. High-stakes decisions still deserve professional review.</p>
<h3>Can I use it on mobile?</h3>
<p>Yes. The public calculator pages are mobile-responsive; just be extra careful with decimal entry on small screens.</p>
HTML;
        }

        return <<<HTML
<h2>FAQ</h2>
{$items}
<p>Still stuck? Re-run the sample path, then your real path, and compare inputs character by character. Most discrepancies hide in a single mistyped digit or a monthly/annual mismatch.</p>
HTML;
    }

    protected function closing(Calculator $c, string $title, string $topic): string
    {
        $urlPath = '/calculators/'.$c->slug;

        return <<<HTML
<h2>Put the {$title} to work</h2>
<p>You now have a full operating manual: why {$topic} math deserves a dedicated tool, how the method behaves, which steps to follow, which examples to rehearse, and which mistakes to refuse. The remaining work is practical—open the calculator, enter honest inputs, and save a scenario you can defend.</p>
<p>Launch the free <strong>{$title}</strong> on AI Calculator Hub, keep this guide nearby for the first few runs, and upgrade your inputs whenever real documents become available. Clear inputs plus a transparent method beat gut feel—every time it matters.</p>
<p><em>Educational use only.</em> Laws, medical guidance, construction codes, and lender policies change. Verify critical actions with qualified professionals and primary source documents. Calculator path reference: {$urlPath}.</p>
HTML;
    }

    protected function extraDepthSection(Calculator $c, string $title, string $topic, string $fieldList, int $pass): string
    {
        $pass++;
        $variants = [
            <<<HTML
<h2>Checklist before you rely on a {$title} result</h2>
<p>Print or save this checklist. Confirm identity of the problem (are you solving for payment, quantity, category, or conversion?). Confirm the audience for the number (yourself, a client, a lender, a teacher). Confirm the date and version of any external rate table you used. Confirm that {$fieldList} came from documents, not memory. Confirm you stored both optimistic and conservative scenarios. Confirm you know the next human expert to call if the decision is irreversible. A calculator accelerates arithmetic; governance of the decision remains human.</p>
<p>Teams that skip checklists relearn the same errors: wrong fiscal year, wrong brick size, wrong height unit, wrong credit hours. The cost is rarely the minute saved; it is the rework after a public commitment.</p>
HTML,
            <<<HTML
<h2>Teaching and documenting {$topic} with this calculator</h2>
<p>If you train interns, students, or junior staff, sit beside them for one guided run of the {$title}. Narrate why each field exists. Ask them to predict the direction of change before they edit an input. Prediction-plus-check builds intuition faster than watching someone else click Calculate.</p>
<p>Documentation templates help: “Inputs… Outputs… Decision… Residual risks… Owner… Date.” Attach that note to procurement emails, HR discussions, or assignment submissions. Future audits become easier when {$topic} work leaves a trail.</p>
HTML,
            <<<HTML
<h2>Edge cases worth a second look</h2>
<p>Zero and near-zero inputs, maximum tenure or area, and boundary category thresholds deserve slow thinking. If the {$title} accepts a wide numeric range, that does not mean every value is physically sensible. Translate extreme outputs back into real-world language: would this EMI leave grocery money? Would this AC tonnage fit the breaker? Would this GPA require retaking a course?</p>
<p>When two authoritative sources disagree on a constant—fee schedule, tax slab, conversion factor—record both, run both, and escalate. Do not silently average conflicting legal constants.</p>
HTML,
            <<<HTML
<h2>From estimate to action plan</h2>
<p>Convert the calculator output into timed actions. If materials are short, the action is an order list. If tax is high, the action may be withholding changes or expense timing. If BMI or calories flag a concern, the action is a clinical conversation—not a crash diet improvised at midnight.</p>
<p>Action plans should include owners and dates. A {$topic} number without an owner becomes trivia. With an owner, it becomes operations.</p>
HTML,
            <<<HTML
<h2>Comparing alternatives using the same {$title}</h2>
<p>Fair comparisons freeze every input except the one under debate. When vendors pitch different rates or sizes, rebuild each offer inside the same calculator rather than trusting incompatible marketing PDFs. Identical structure reveals real differences.</p>
<p>Export or write down the paired scenarios side by side. Decision meetings go better when everyone stares at the same {$fieldList} assumptions.</p>
HTML,
            <<<HTML
<h2>Privacy, savings, and revisiting results</h2>
<p>Prefer saving scenarios in your account when available instead of pasting sensitive salary or health numbers into random chats. Revisit quarterly if your {$topic} drivers change—new salary, new room, new syllabus, new fee notice.</p>
<p>The {$title} will still be here; your life variables will not freeze. Schedule a reminder to re-run critical calculations when source documents update.</p>
HTML,
            <<<HTML
<h2>Quality bar for publishing or sharing numbers</h2>
<p>Before you paste a result into a client report, blog, or classroom submission, re-read the inputs aloud. Have a second person spot-check units. Include the calculator name and date. That quality bar prevents embarrassing reversals and builds a reputation for careful quantitative communication.</p>
<p>Remember that clarity beats theatrical precision. Prefer honest ranges when inputs are uncertain. The {$title} can still anchor the midpoint of a responsible range.</p>
HTML,
            <<<HTML
<h2>Continuing education around {$topic}</h2>
<p>After you master the basic run, deepen domain literacy: read the primary regulation, standard, or textbook chapter that justifies the method. Calculators operationalize knowledge; they do not replace it. Pair each major decision with one external reading and one expert conversation when stakes are high.</p>
<p>That habit—tool plus source plus expert—keeps {$topic} work both fast and humble.</p>
HTML,
        ];

        return $variants[$pass % count($variants)];
    }

    protected function trimToWordBudget(string $html, int $budget): string
    {
        if ($this->wordCount($html) <= $budget) {
            return $html;
        }

        // Drop optional mid/late sections first (keep lead, how-to, examples, closing).
        $dropPatterns = [
            '/<h2>Continuing education[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Quality bar[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Privacy, savings[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Comparing alternatives[\s\S]*?(?=<h2>|$)/i',
            '/<h2>From estimate to action[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Edge cases[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Teaching and documenting[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Checklist before[\s\S]*?(?=<h2>|$)/i',
            '/<h2>How to interpret the output responsibly[\s\S]*?(?=<h2>|$)/i',
            '/<h2>Accuracy tips[\s\S]*?(?=<h2>|$)/i',
        ];

        foreach ($dropPatterns as $pattern) {
            if ($this->wordCount($html) <= $budget) {
                break;
            }
            $html = preg_replace($pattern, '', $html, 1) ?? $html;
        }

        // Remove trailing paragraphs inside remaining sections (except final closing h2).
        while ($this->wordCount($html) > $budget) {
            if (! preg_match('/(?s)(.*)<p>(?:(?!Put the).)*?<\/p>(\s*<h2>Put the[\s\S]*)?$/u', $html, $m)) {
                break;
            }
            $next = ($m[1] ?? '').($m[2] ?? '');
            if ($next === $html || $this->wordCount($next) < 1000) {
                break;
            }
            $html = $next;
        }

        return trim($html);
    }

    /**
     * @return list<string>
     */
    protected function fieldLabels(Calculator $calculator): array
    {
        $labels = [];
        foreach ($calculator->input_schema ?? [] as $field) {
            $label = trim((string) ($field['label'] ?? $field['name'] ?? ''));
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return array_values(array_unique($labels));
    }

    /**
     * @param  list<string>  $items
     */
    protected function oxford(array $items): string
    {
        $items = array_values($items);
        $count = count($items);
        if ($count === 0) {
            return 'your values';
        }
        if ($count === 1) {
            return $items[0];
        }
        if ($count === 2) {
            return $items[0].' and '.$items[1];
        }
        $last = array_pop($items);

        return implode(', ', $items).', and '.$last;
    }

    protected function mapBlogCategory(string $calculatorCategorySlug): string
    {
        return match ($calculatorCategorySlug) {
            'construction' => 'construction-tips',
            'finance', 'climate-energy' => 'finance-guides',
            'health', 'fitness' => 'health-fitness',
            'education' => 'education',
            'nepal' => 'nepal-guides',
            default => 'how-to-calculators',
        };
    }

    /**
     * @return list<string>
     */
    protected function mapTags(string $calculatorCategorySlug): array
    {
        $base = ['how-to', 'beginners'];

        return match ($calculatorCategorySlug) {
            'construction' => array_merge($base, ['construction', 'estimation', 'materials']),
            'finance' => array_merge($base, ['finance', 'investing']),
            'health', 'fitness' => array_merge($base, ['health', 'fitness']),
            'education' => array_merge($base, ['education']),
            'nepal' => array_merge($base, ['nepal', 'daily-life']),
            'climate-energy' => array_merge($base, ['estimation', 'daily-life']),
            'unit-conversion' => array_merge($base, ['units', 'daily-life']),
            'home' => array_merge($base, ['estimation', 'daily-life']),
            'developer' => array_merge($base, ['business']),
            default => array_merge($base, ['daily-life']),
        };
    }
}
