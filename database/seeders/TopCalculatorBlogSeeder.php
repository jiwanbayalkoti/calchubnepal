<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\Calculator;
use App\Models\User;
use App\Services\Seo\TopCalculatorBlogContentBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Creates long-form (1000–1400 word) SEO how-to posts for the top 20 calculators.
 *
 * Publishing is staggered one post per day starting today at 10:00 so
 * BlogPost::scopePublished() automatically reveals new content daily.
 *
 * Safe to re-run (upsert by slug). Does not delete other blog posts.
 */
class TopCalculatorBlogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $author = User::query()->where('email', 'admin@calculatorhub.com')->first()
                ?? User::query()->orderBy('id')->first();

            if (! $author) {
                $this->command?->warn('TopCalculatorBlogSeeder: no users found. Seed AdminUserSeeder first.');

                return;
            }

            // Ensure blog categories/tags exist without rewriting all legacy posts every time.
            if (BlogCategory::query()->count() < 3 || BlogTag::query()->count() < 5) {
                $this->call(BlogSeeder::class);
            }

            $categoryIds = BlogCategory::query()->pluck('id', 'slug')->all();
            if ($categoryIds === []) {
                $this->call(BlogSeeder::class);
                $categoryIds = BlogCategory::query()->pluck('id', 'slug')->all();
            }
            $tagIds = BlogTag::query()->pluck('id', 'slug')->all();

            $calculators = Calculator::query()
                ->with([
                    'category:id,slug,name',
                    'faqs' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')->limit(6),
                ])
                ->where('is_active', true)
                ->orderByDesc('usage_count')
                ->orderByDesc('views_count')
                ->orderBy('id')
                ->take(20)
                ->get();

            if ($calculators->count() < 1) {
                $this->command?->warn('TopCalculatorBlogSeeder: no calculators found.');

                return;
            }

            $builder = app(TopCalculatorBlogContentBuilder::class);
            $start = now()->startOfDay()->setTime(10, 0, 0);
            $created = 0;
            $updated = 0;
            $words = [];

            foreach ($calculators->values() as $index => $calculator) {
                $article = $builder->build($calculator, $index);
                $publishAt = $start->copy()->addDays($index);

                $existing = BlogPost::withTrashed()->where('slug', $article['slug'])->first();

                $payload = [
                    'blog_category_id' => $categoryIds[$article['category']] ?? ($categoryIds['how-to-calculators'] ?? null),
                    'user_id' => $author->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'featured_image' => $existing?->featured_image,
                    'meta_title' => $article['meta_title'],
                    'meta_description' => $article['meta_description'],
                    'meta_keywords' => $article['meta_keywords'],
                    'status' => BlogPost::STATUS_PUBLISHED,
                    'published_at' => $publishAt,
                    'reading_time_minutes' => $article['reading_time_minutes'],
                    'is_featured' => $article['is_featured'],
                    'ai_generated' => false,
                    'created_by' => $existing?->created_by ?? $author->id,
                    'updated_by' => $author->id,
                    'deleted_at' => null,
                ];

                $post = BlogPost::withTrashed()->updateOrCreate(
                    ['slug' => $article['slug']],
                    $payload
                );

                if ($existing) {
                    $updated++;
                } else {
                    $created++;
                }

                $tagIdList = array_values(array_filter(
                    array_map(fn (string $slug) => $tagIds[$slug] ?? null, $article['tags'])
                ));
                $post->tags()->sync($tagIdList);

                $calculatorIds = Calculator::query()
                    ->whereIn('slug', $article['calculators'])
                    ->pluck('id')
                    ->all();
                $post->calculators()->sync($calculatorIds);

                $words[] = [
                    'day' => $index,
                    'publish' => $publishAt->toDateString(),
                    'slug' => $post->slug,
                    'words' => $article['word_count'],
                    'calc' => $calculator->slug,
                ];
            }

            $min = collect($words)->min('words');
            $max = collect($words)->max('words');
            $this->command?->info("TopCalculatorBlogSeeder: created={$created}, updated={$updated}, posts=".count($words).", words={$min}-{$max}");

            foreach ($words as $row) {
                $flag = ($row['words'] < 1000 || $row['words'] > 1400) ? ' OUT_OF_RANGE' : '';
                $this->command?->line(sprintf(
                    '  D+%d %s [%d words]%s — %s',
                    $row['day'],
                    $row['publish'],
                    $row['words'],
                    $flag,
                    $row['slug']
                ));
            }

            if ($min < 1000 || $max > 1400) {
                throw new \RuntimeException("TopCalculatorBlogSeeder: word count out of 1000–1400 range (min={$min}, max={$max}).");
            }
        });
    }
}
