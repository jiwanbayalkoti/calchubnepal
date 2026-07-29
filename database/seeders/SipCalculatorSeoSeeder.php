<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\CalculatorExample;
use App\Models\CalculatorFaq;
use App\Services\Calculators\CalculatorRegistry;
use Database\Seeders\Content\SipCalculatorSeoContent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Applies SIP calculator SEO content + strengthens the related beginner blog post.
 * Safe to re-run. Use locally first, then on live: php artisan db:seed --class=SipCalculatorSeoSeeder
 */
class SipCalculatorSeoSeeder extends Seeder
{
    public function run(CalculatorRegistry $registry): void
    {
        DB::transaction(function () use ($registry): void {
            $this->updateCalculator($registry);
            $this->updateBlogPost();
        });

        $this->command?->info('SIP calculator SEO content updated (calculator + blog).');
    }

    protected function updateCalculator(CalculatorRegistry $registry): void
    {
        $meta = SipCalculatorSeoContent::meta();
        $calculator = Calculator::query()->where('slug', 'sip-calculator')->first();

        if (! $calculator) {
            $this->command?->warn('sip-calculator not found — run CalculatorSeeder first.');

            return;
        }

        $calculator->update([
            'title' => $meta['title'],
            'short_description' => $meta['short_description'],
            'description' => $meta['description'],
            'formula_description' => $meta['formula_description'],
            'formula_expression' => $meta['formula_expression'],
            'meta_title' => $meta['meta_title'],
            'meta_description' => $meta['meta_description'],
            'meta_keywords' => $meta['meta_keywords'],
            'is_featured' => true,
            'is_active' => true,
        ]);

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

        $handler = $registry->get('sip_calculator');
        $inputs = [
            'monthly_investment' => 5000,
            'expected_annual_return' => 12,
            'investment_period_years' => 10,
        ];
        $outputs = [];

        try {
            $outputs = $handler->calculate($inputs)['results'] ?? [];
        } catch (Throwable $e) {
            $this->command?->warn('SIP example calculate failed: '.$e->getMessage());
        }

        $calculator->examples()->delete();
        CalculatorExample::query()->create([
            'calculator_id' => $calculator->id,
            'title' => 'Example: ₹5,000/month SIP for 10 years at 12%',
            'inputs' => $inputs,
            'outputs' => $outputs,
            'explanation' => $meta['example_explanation'],
            'sort_order' => 1,
        ]);
    }

    protected function updateBlogPost(): void
    {
        $post = BlogPost::query()->where('slug', 'sip-and-compound-interest-for-beginners')->first();

        if (! $post) {
            $this->command?->warn('SIP blog post not found — skipped.');

            return;
        }

        $post->update([
            'title' => 'How to Use a SIP Calculator (With Examples & Compound Interest Basics)',
            'excerpt' => 'Learn how a SIP calculator estimates maturity value, see real examples (₹5,000/month), and understand how compound interest powers systematic investment plans.',
            'meta_title' => 'How to Use SIP Calculator With Examples | Free Tool Guide',
            'meta_description' => 'Step-by-step guide to using a SIP calculator: formula, ₹5,000/month example, SIP vs lump sum, and tips. Free SIP return calculator linked inside.',
            'meta_keywords' => 'sip calculator, how to use sip calculator, sip return calculator, calculate sip returns, systematic investment plan, compound interest',
            'reading_time_minutes' => 12,
            'is_featured' => true,
            'content' => <<<'HTML'
<p>A <strong>SIP calculator</strong> helps you estimate how monthly investments can grow over time. Whether you are planning for retirement, a child’s education, or a home down payment, projecting <strong>SIP returns</strong> before you invest makes goals clearer—and more realistic.</p>

<h2>What is a Systematic Investment Plan (SIP)?</h2>
<p>A SIP invests a fixed amount at regular intervals (usually monthly) into a mutual fund or similar product. Combined with compounding—earning returns on both your contributions and prior gains—consistent investing can build meaningful wealth over long horizons.</p>

<h2>How to use our free SIP calculator</h2>
<ol>
<li>Open the <a href="/calculator/sip-calculator"><strong>SIP Calculator</strong></a>.</li>
<li>Enter <strong>monthly investment</strong> (for example 5,000).</li>
<li>Enter <strong>expected annual return</strong> (%) based on your risk profile.</li>
<li>Enter <strong>investment period</strong> in years.</li>
<li>Click Calculate to see maturity value, total invested, and wealth gained.</li>
</ol>

<h2>SIP formula (simple explanation)</h2>
<p>For monthly SIPs, maturity is estimated with:</p>
<p><code>FV = P × [((1 + r)^n − 1) / r] × (1 + r)</code></p>
<p>Where <strong>P</strong> is the monthly amount, <strong>r</strong> is the monthly rate, and <strong>n</strong> is the number of months. <strong>Wealth gained</strong> = maturity − total invested.</p>

<h2>Worked example: ₹5,000 per month for 10 years</h2>
<p>Assume 12% expected annual return:</p>
<ul>
<li>Total invested = 600,000</li>
<li>Estimated maturity ≈ 1,161,695</li>
<li>Estimated wealth gained ≈ 561,695</li>
</ul>
<p>Try the same numbers in the <a href="/calculator/sip-calculator">monthly SIP calculator</a> to verify. Remember: markets do not deliver a fixed 12% every year—use the rate as a planning assumption only.</p>

<h2>SIP vs lump sum</h2>
<p>SIPs spread purchases over time (rupee-cost averaging). A lump sum invests everything at once and may grow faster if markets rise immediately after you invest. Compare lump-sum growth with the <a href="/calculator/compound-interest-calculator">compound interest calculator</a>.</p>

<h2>Practical tips before you start a SIP</h2>
<ul>
<li>Define the goal and the year you need the money.</li>
<li>Pick an asset mix you can hold through volatility.</li>
<li>Automate contributions; increase them when income rises.</li>
<li>Review once or twice a year—not every market headline.</li>
</ul>

<h2>Next step</h2>
<p>Run your own scenarios on the <a href="/calculator/sip-calculator"><strong>SIP maturity calculator</strong></a>, then stress-test a lower return rate so your plan still works if markets underperform.</p>
HTML,
        ]);
    }
}
