<?php

namespace App\Console\Commands;

use App\Models\Calculator;
use App\Models\CalculatorFaq;
use App\Services\Seo\CalculatorContentBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EnrichCalculatorContentCommand extends Command
{
    protected $signature = 'calculators:enrich-content {--force : Rewrite even non-thin descriptions}';

    protected $description = 'Generate unique SEO descriptions and FAQs for thin calculator pages (AdSense readiness)';

    public function handle(CalculatorContentBuilder $builder): int
    {
        $force = (bool) $this->option('force');
        $updated = 0;

        Calculator::query()
            ->with('category:id,name')
            ->orderBy('id')
            ->chunkById(50, function ($calculators) use ($builder, $force, &$updated) {
                foreach ($calculators as $calculator) {
                    if (! $force && ! $builder->isThin($calculator->description)) {
                        continue;
                    }

                    $key = $calculator->formula_key ?: str_replace('-', '_', $calculator->slug);
                    $meta = $builder->build(
                        $key,
                        $calculator->title,
                        $calculator->category?->name,
                        is_array($calculator->input_schema) ? $calculator->input_schema : [],
                    );

                    DB::transaction(function () use ($calculator, $meta, &$updated) {
                        $calculator->update([
                            'short_description' => $meta['short_description'],
                            'description' => $meta['description'],
                            'formula_description' => $meta['formula_description'],
                            'meta_title' => $meta['meta_title'],
                            'meta_description' => $meta['meta_description'],
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

                        $updated++;
                    });
                }
            });

        $this->info("Enriched {$updated} calculator(s).");

        return self::SUCCESS;
    }
}
