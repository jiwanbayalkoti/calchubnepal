<?php

namespace App\Console\Commands;

use App\Models\Calculator;
use App\Models\CalculatorExample;
use App\Models\CalculatorFaq;
use App\Services\Calculators\CalculatorRegistry;
use App\Services\Calculators\ConfigurableCalculatorHandler;
use App\Services\Calculators\DynamicStubHandler;
use App\Services\Seo\CalculatorContentBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Activates every catalog-driven stub calculator: refreshes its input
 * schema / validation rules from the live handler, backfills SEO content
 * for thin pages, and rebuilds one worked example + FAQ set so the page
 * never shows a "Coming soon" placeholder.
 */
class ActivateCalculatorStubsCommand extends Command
{
    protected $signature = 'calculators:activate-stubs {--force : Rewrite SEO content and FAQs even when not thin}';

    protected $description = 'Activate catalog-driven formula calculators: sync schema, SEO and examples for every stub';

    public function handle(CalculatorRegistry $registry, CalculatorContentBuilder $builder): int
    {
        $force = (bool) $this->option('force');

        $activated = 0;
        $skippedStillStub = [];

        Calculator::query()
            ->with('category:id,name')
            ->orderBy('id')
            ->chunkById(50, function ($calculators) use ($registry, $builder, $force, &$activated, &$skippedStillStub) {
                foreach ($calculators as $calculator) {
                    $key = $calculator->formula_key ?: str_replace('-', '_', $calculator->slug);

                    try {
                        $handler = $registry->get($key);
                    } catch (Throwable) {
                        continue;
                    }

                    if ($handler instanceof DynamicStubHandler) {
                        $skippedStillStub[] = $calculator->slug;

                        continue;
                    }

                    if (! $handler instanceof ConfigurableCalculatorHandler) {
                        // Already served by a dedicated bespoke handler class — leave untouched.
                        continue;
                    }

                    $this->activateOne($calculator, $handler, $builder, $force);
                    $activated++;
                }
            });

        $this->info("Activated {$activated} catalog-driven calculator(s).");

        if ($skippedStillStub !== []) {
            $this->warn('Still using DynamicStubHandler ('.count($skippedStillStub).'): '.implode(', ', array_slice($skippedStillStub, 0, 20)));
        } else {
            $this->info('No calculators remain on DynamicStubHandler.');
        }

        $this->applyPensionSeo();

        return self::SUCCESS;
    }

    protected function activateOne(Calculator $calculator, ConfigurableCalculatorHandler $handler, CalculatorContentBuilder $builder, bool $force): void
    {
        $schema = $handler->inputSchema();
        $rules = $handler->validationRules();
        $formulaMeta = $this->handlerFormulaDescription($handler);

        DB::transaction(function () use ($calculator, $handler, $builder, $force, $schema, $rules, $formulaMeta) {
            $calculator->input_schema = $schema;
            $calculator->validation_rules = $rules;
            $calculator->is_active = true;

            if (filled($formulaMeta)) {
                $calculator->formula_description = $formulaMeta;
            }

            $needsSeo = $force || $builder->isThin($calculator->description);

            if ($needsSeo) {
                $meta = $builder->build(
                    $handler->key(),
                    $calculator->title,
                    $calculator->category?->name,
                    $schema,
                );

                $calculator->short_description = $meta['short_description'];
                $calculator->description = $meta['description'];
                $calculator->formula_description = $formulaMeta ?: $meta['formula_description'];
                $calculator->meta_title = $meta['meta_title'];
                $calculator->meta_description = $meta['meta_description'];
            }

            $calculator->save();

            $existingFaqCount = $calculator->faqs()->count();

            if ($force || $existingFaqCount < 3) {
                $meta ??= $builder->build($handler->key(), $calculator->title, $calculator->category?->name, $schema);

                $calculator->faqs()->delete();

                foreach ($meta['faqs'] as $index => [$question, $answer]) {
                    CalculatorFaq::query()->create([
                        'calculator_id' => $calculator->id,
                        'question' => $question,
                        'answer' => $answer,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            }

            $this->rebuildExample($calculator, $handler, $schema);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $schema
     */
    protected function rebuildExample(Calculator $calculator, ConfigurableCalculatorHandler $handler, array $schema): void
    {
        $inputs = [];

        foreach ($schema as $field) {
            $inputs[$field['name']] = $field['default'] ?? $this->fallbackValueForType($field['type'] ?? 'number');
        }

        try {
            $result = $handler->calculate($inputs);
        } catch (Throwable) {
            return;
        }

        $calculator->examples()->delete();

        CalculatorExample::query()->create([
            'calculator_id' => $calculator->id,
            'title' => 'Sample Calculation',
            'inputs' => $inputs,
            'outputs' => $result['results'] ?? [],
            'explanation' => $result['breakdown']['formula'] ?? 'Worked example using the default values shown above.',
            'sort_order' => 1,
        ]);
    }

    protected function fallbackValueForType(string $type): mixed
    {
        return match ($type) {
            'number', 'integer' => 1,
            'boolean' => true,
            'date' => now()->toDateString(),
            'time' => '07:00',
            default => '',
        };
    }

    protected function handlerFormulaDescription(ConfigurableCalculatorHandler $handler): ?string
    {
        $reflection = new \ReflectionClass($handler);
        $property = $reflection->getProperty('definition');
        $property->setAccessible(true);
        /** @var array<string, mixed> $definition */
        $definition = $property->getValue($handler);

        return $definition['meta']['formula'] ?? null;
    }

    /**
     * Bespoke, hand-written SEO for the flagship pension decision calculator
     * (both slug variants point at the same underlying tool).
     */
    protected function applyPensionSeo(): void
    {
        $description = <<<'HTML'
<h2>Should You Take the Pension Lump Sum or Monthly Payments?</h2>
<p>When a pension plan offers you a choice between a one-time lump-sum payout and a stream of monthly payments for life, the decision is really a present-value comparison: which option is worth more <em>today</em>, given how long you expect to receive payments and what you could otherwise earn on the lump sum?</p>
<p>This calculator computes the Net Present Value (NPV) of the monthly annuity option — including an optional annual cost-of-living adjustment (COLA) — and compares it directly against the lump-sum offer. It also estimates a breakeven age and shows how the decision changes if you live to 75, 85 or 95.</p>

<h2>How the NPV Calculation Works</h2>
<p>The tool discounts every future monthly payment back to today's dollars using your assumed discount rate (typically the return you could realistically earn by investing the lump sum yourself). If your pension includes a COLA, payments grow slightly every year, which increases the present value relative to a flat annuity.</p>
<p>The formula used is the present value of a growing ordinary annuity:</p>
<p><code>PV = PMT × (1 − ((1+g)/(1+r))^n) / (r − g)</code></p>
<p>where <code>r</code> is the monthly discount rate, <code>g</code> is the monthly COLA rate, and <code>n</code> is the total number of monthly payments between your current age and your life expectancy.</p>

<h2>Why Life Expectancy Matters So Much</h2>
<p>The single biggest driver of this decision is how long you live. The longer your horizon, the more total payments you collect, and the higher the present value of the monthly option relative to the lump sum. That's why this calculator always shows a sensitivity table at life expectancy 75, 85 and 95 — so you can see how fragile (or robust) the recommendation is to that one uncertain assumption.</p>

<h2>What the Discount Rate Assumption Means</h2>
<p>Your discount rate should reflect a realistic, risk-adjusted return on the lump sum if you invested it instead of taking the annuity. A higher discount rate favors the lump sum (because you're assuming you can grow that money faster than the pension pays out); a lower discount rate favors the monthly annuity (because the guaranteed payments are worth relatively more).</p>

<h2>Other Factors This Calculator Doesn't Capture</h2>
<ul>
<li>Taxes may differ between a lump-sum rollover and ongoing pension income.</li>
<li>Pension payments are typically guaranteed for life (longevity insurance) — a lump sum carries market and longevity risk you must manage yourself.</li>
<li>Survivor/spousal benefit elections can change the effective monthly payment.</li>
<li>Some pensions are insured up to limits by the PBGC (in the U.S.); a struggling plan sponsor could be a reason to prefer the lump sum.</li>
</ul>
<p>Use this calculator as a starting point for the math, then discuss your specific plan's rules, survivor options and tax treatment with a fee-only financial advisor before making an irreversible election.</p>
HTML;

        $faqs = [
            [
                'What is the difference between a pension lump sum and a monthly annuity?',
                'A lump sum is a single, one-time payment you receive (and then manage yourself), while a monthly annuity is a guaranteed stream of payments — usually for the rest of your life. The lump sum gives you flexibility and control; the monthly annuity gives you guaranteed, predictable income with no investment risk on your part.',
            ],
            [
                'How is the Net Present Value (NPV) of the monthly pension calculated?',
                'The calculator discounts every future monthly payment back to today using your chosen discount rate, and adds a cost-of-living adjustment (COLA) if your plan includes one. The formula is PV = PMT × (1 − ((1+g)/(1+r))^n) / (r − g), the standard present value of a growing annuity.',
            ],
            [
                'What discount rate should I use?',
                'Use a rate close to the realistic, long-term return you expect if you invested the lump sum yourself (net of fees), adjusted for the risk you are comfortable taking. Many people use 4–7% as a starting point. A higher rate favors the lump sum; a lower rate favors the monthly annuity.',
            ],
            [
                'What if I don\'t know my life expectancy?',
                'Nobody knows their exact life expectancy, which is exactly why this calculator shows results at ages 75, 85 and 95. If the monthly annuity wins at all three ages, it is the more robust choice. If the lump sum wins at all three, it is more robust. If the answer flips somewhere in between, the decision is genuinely close and other factors (guaranteed income preference, health, family longevity) should tip the scale.',
            ],
            [
                'Does this calculator account for taxes?',
                'No — both the lump sum and the monthly payments are typically taxable as ordinary income, but the exact treatment depends on whether you roll the lump sum into an IRA (deferring tax) or take it as cash (immediate tax + possible penalties). Consult a tax professional for your specific situation before choosing.',
            ],
            [
                'What is a breakeven age and how is it used here?',
                'The breakeven age is a simplified estimate of the age at which the total monthly payments received would equal the lump-sum amount, ignoring investment growth (Lump Sum ÷ Monthly Payment ÷ 12 + Current Age). It is a quick sanity check, not a substitute for the full NPV comparison above it.',
            ],
            [
                'Is the monthly pension option safer than taking the lump sum?',
                'Generally yes, in the sense that a monthly pension removes market risk and the risk of outliving your savings — the plan (or its insurer) bears that risk instead of you. A lump sum shifts investment risk, inflation risk and longevity risk onto you, in exchange for flexibility and potential upside.',
            ],
            [
                'Should I include a cost-of-living adjustment (COLA) if my pension has one?',
                'Yes — even a small annual COLA (1–3%) meaningfully increases the present value of the monthly option over a multi-decade retirement, because payments compound higher every year rather than staying flat. Always enter your plan\'s actual COLA percentage if it has one; enter 0 if it does not.',
            ],
        ];

        $count = Calculator::query()
            ->whereIn('slug', ['pension-lump-sum-vs-annuity-calculator', 'pension-lump-sum-vs-monthly-decision-calculator'])
            ->get()
            ->each(function (Calculator $calculator) use ($description, $faqs) {
                $seo = match ($calculator->slug) {
                    'pension-lump-sum-vs-monthly-decision-calculator' => [
                        'meta_title' => 'Pension Lump Sum vs Monthly Decision - Breakeven Age Tool',
                        'meta_description' => 'Decide pension lump sum vs monthly payments with NPV, breakeven age and longevity sensitivity at ages 75, 85 and 95.',
                        'meta_keywords' => 'pension lump sum vs monthly, pension decision calculator, pension breakeven age, longevity sensitivity, pension NPV',
                    ],
                    default => [
                        'meta_title' => 'Pension Lump Sum vs Annuity Calculator - Compare Offers Free',
                        'meta_description' => 'Compare pension lump-sum vs monthly annuity NPV, breakeven age and longevity. Free calculator with COLA and discount rate.',
                        'meta_keywords' => 'pension lump sum vs annuity, pension calculator, pension buyout calculator, monthly pension vs lump sum, pension NPV calculator, pension breakeven age',
                    ],
                };

                $calculator->update([
                    ...$seo,
                    'description' => $description,
                ]);

                $calculator->faqs()->delete();

                foreach ($faqs as $index => [$question, $answer]) {
                    CalculatorFaq::query()->create([
                        'calculator_id' => $calculator->id,
                        'question' => $question,
                        'answer' => $answer,
                        'sort_order' => $index + 1,
                        'is_active' => true,
                    ]);
                }
            })
            ->count();

        if ($count > 0) {
            $this->info("Applied rich pension SEO content to {$count} calculator page(s).");
        }
    }
}
