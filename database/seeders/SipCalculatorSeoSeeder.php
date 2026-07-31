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

        $content = \Database\Seeders\Content\GuideBlogs\SipAndCompoundInterestForBeginners::html();
        $words = \Database\Seeders\Content\GuideBlogContentMap::wordCount($content);

        $post->update([
            'title' => 'How to Use a SIP Calculator (With Examples & Compound Interest Basics)',
            'excerpt' => 'Learn how a SIP calculator estimates maturity value, see real examples (₹5,000/month), and understand how compound interest powers systematic investment plans.',
            'meta_title' => 'How to Use SIP Calculator With Examples | Free Tool Guide',
            'meta_description' => 'Step-by-step guide to using a SIP calculator: formula, ₹5,000/month example, SIP vs lump sum, and tips. Free SIP return calculator linked inside.',
            'meta_keywords' => 'sip calculator, how to use sip calculator, sip return calculator, calculate sip returns, systematic investment plan, compound interest',
            'reading_time_minutes' => max(12, (int) ceil($words / 200)),
            'is_featured' => true,
            'content' => $content,
        ]);
    }
}
