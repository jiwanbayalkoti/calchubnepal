<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\Seo\QrGuideBlogContentBuilder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * One SEO blog per QR type. Publishes 2 posts/day starting today (09:00 & 15:00).
 */
class QrGuideBlogSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $author = User::query()->where('email', 'admin@calculatorhub.com')->first()
                ?? User::query()->orderBy('id')->first();

            if (! $author) {
                $this->command?->warn('QrGuideBlogSeeder: no users found.');

                return;
            }

            $category = BlogCategory::query()->updateOrCreate(
                ['slug' => 'qr-code-guides'],
                [
                    'name' => 'QR Code Guides',
                    'description' => 'SEO how-to guides for every QR type — URL, WiFi, WhatsApp, eSewa, maps, payments and more.',
                    'meta_title' => 'QR Code Guides & How-Tos | CalchubNepal',
                    'meta_description' => 'Step-by-step QR code tutorials with top SEO keywords for URL, WiFi, WhatsApp, bank, eSewa, maps and more.',
                    'is_active' => true,
                    'sort_order' => 7,
                    'created_by' => $author->id,
                    'updated_by' => $author->id,
                ]
            );

            $builder = app(QrGuideBlogContentBuilder::class);
            $types = $builder->publishOrder();
            $start = now()->startOfDay();
            $created = 0;
            $updated = 0;

            foreach ($types as $index => $type) {
                $article = $builder->build($type);
                $dayOffset = intdiv($index, 2);
                $hour = ($index % 2 === 0) ? 9 : 15;
                $publishAt = $start->copy()->addDays($dayOffset)->setTime($hour, 0, 0);

                $tagIds = [];
                foreach ($article['tags'] as $tagName) {
                    $tagSlug = Str::slug($tagName);
                    if ($tagSlug === '') {
                        continue;
                    }
                    $tag = BlogTag::query()->updateOrCreate(
                        ['slug' => $tagSlug],
                        ['name' => Str::title($tagName)]
                    );
                    $tagIds[] = $tag->id;
                }

                // Stable type tag for filtering
                $typeTag = BlogTag::query()->updateOrCreate(
                    ['slug' => 'qr-type-'.$type->value],
                    ['name' => 'QR: '.$type->label()]
                );
                $tagIds[] = $typeTag->id;

                $existing = BlogPost::withTrashed()->where('slug', $article['slug'])->first()
                    ?? BlogPost::withTrashed()->where('related_qr_type', $type->value)->first();

                $payload = [
                    'blog_category_id' => $category->id,
                    'user_id' => $author->id,
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'featured_image' => $existing?->featured_image,
                    'meta_title' => $article['meta_title'],
                    'meta_description' => $article['meta_description'],
                    'meta_keywords' => $article['meta_keywords'],
                    'related_qr_type' => $type->value,
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

                // If an older row matched by related_qr_type with different slug, soft-clean duplicate
                if ($existing && $existing->id !== $post->id && $existing->related_qr_type === $type->value) {
                    $existing->delete();
                }

                $post->tags()->sync(array_values(array_unique($tagIds)));

                if ($existing && $existing->id === $post->id) {
                    $updated++;
                } else {
                    $created++;
                }
            }

            $this->command?->info("QrGuideBlogSeeder: {$created} created, {$updated} updated (2 posts/day from {$start->toDateString()}).");
        });
    }
}
