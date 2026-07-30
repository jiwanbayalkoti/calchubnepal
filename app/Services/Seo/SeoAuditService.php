<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Models\SeoPage;
use Illuminate\Support\Collection;

/**
 * Produces an admin SEO health report (missing meta, duplicates, thin content).
 */
class SeoAuditService
{
    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        $calculators = Calculator::query()
            ->where('is_active', true)
            ->get(['id', 'title', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'description', 'og_image']);

        $posts = BlogPost::query()
            ->published()
            ->get(['id', 'title', 'slug', 'meta_title', 'meta_description', 'meta_keywords', 'featured_image', 'excerpt']);

        $categories = CalculatorCategory::query()
            ->where('is_active', true)
            ->get(['id', 'name', 'slug', 'meta_title', 'meta_description']);

        $builder = app(CalculatorContentBuilder::class);

        return [
            'summary' => [
                'calculators' => $calculators->count(),
                'blog_posts' => $posts->count(),
                'categories' => $categories->count(),
                'missing_titles' => $this->missingTitles($calculators, $posts, $categories)->count(),
                'missing_descriptions' => $this->missingDescriptions($calculators, $posts, $categories)->count(),
                'duplicate_titles' => count($this->duplicateTitles($calculators, $posts)),
                'thin_calculators' => $calculators->filter(fn ($c) => $builder->isThin($c->description))->count(),
                'calculators_without_faqs' => $this->calculatorsWithoutFaqs(),
                'calculators_without_canonical' => $calculators->whereNull('canonical_url')->filter(fn ($c) => blank($c->canonical_url))->count(),
            ],
            'missing_titles' => $this->missingTitles($calculators, $posts, $categories)->values(),
            'missing_descriptions' => $this->missingDescriptions($calculators, $posts, $categories)->values(),
            'duplicate_titles' => $this->duplicateTitles($calculators, $posts),
            'thin_calculators' => $calculators
                ->filter(fn ($c) => $builder->isThin($c->description))
                ->take(40)
                ->map(fn ($c) => [
                    'type' => 'calculator',
                    'title' => $c->title,
                    'slug' => $c->slug,
                    'url' => route('calculators.show', $c),
                ])
                ->values(),
            'missing_og_images' => $calculators
                ->filter(fn ($c) => blank($c->og_image))
                ->take(30)
                ->map(fn ($c) => [
                    'type' => 'calculator',
                    'title' => $c->title,
                    'url' => route('calculators.show', $c),
                ])
                ->values(),
            'seo_pages' => SeoPage::query()->count(),
        ];
    }

    /**
     * @param  Collection<int, Calculator>  $calculators
     * @param  Collection<int, BlogPost>  $posts
     * @param  Collection<int, CalculatorCategory>  $categories
     * @return Collection<int, array<string, string>>
     */
    protected function missingTitles(Collection $calculators, Collection $posts, Collection $categories): Collection
    {
        $rows = collect();

        foreach ($calculators as $c) {
            if (blank($c->meta_title)) {
                $rows->push(['type' => 'calculator', 'title' => $c->title, 'url' => route('calculators.show', $c)]);
            }
        }
        foreach ($posts as $p) {
            if (blank($p->meta_title)) {
                $rows->push(['type' => 'blog', 'title' => $p->title, 'url' => route('blog.show', $p)]);
            }
        }
        foreach ($categories as $cat) {
            if (blank($cat->meta_title)) {
                $rows->push(['type' => 'category', 'title' => $cat->name, 'url' => route('categories.show', $cat)]);
            }
        }

        return $rows;
    }

    /**
     * @param  Collection<int, Calculator>  $calculators
     * @param  Collection<int, BlogPost>  $posts
     * @param  Collection<int, CalculatorCategory>  $categories
     * @return Collection<int, array<string, string>>
     */
    protected function missingDescriptions(Collection $calculators, Collection $posts, Collection $categories): Collection
    {
        $rows = collect();

        foreach ($calculators as $c) {
            if (blank($c->meta_description)) {
                $rows->push(['type' => 'calculator', 'title' => $c->title, 'url' => route('calculators.show', $c)]);
            }
        }
        foreach ($posts as $p) {
            if (blank($p->meta_description)) {
                $rows->push(['type' => 'blog', 'title' => $p->title, 'url' => route('blog.show', $p)]);
            }
        }
        foreach ($categories as $cat) {
            if (blank($cat->meta_description)) {
                $rows->push(['type' => 'category', 'title' => $cat->name, 'url' => route('categories.show', $cat)]);
            }
        }

        return $rows;
    }

    /**
     * @param  Collection<int, Calculator>  $calculators
     * @param  Collection<int, BlogPost>  $posts
     * @return list<array{title: string, count: int, urls: list<string>}>
     */
    protected function duplicateTitles(Collection $calculators, Collection $posts): array
    {
        $map = [];

        foreach ($calculators as $c) {
            $title = trim((string) ($c->meta_title ?: $c->title));
            $map[$title][] = route('calculators.show', $c);
        }
        foreach ($posts as $p) {
            $title = trim((string) ($p->meta_title ?: $p->title));
            $map[$title][] = route('blog.show', $p);
        }

        $dupes = [];
        foreach ($map as $title => $urls) {
            if (count($urls) > 1) {
                $dupes[] = [
                    'title' => $title,
                    'count' => count($urls),
                    'urls' => $urls,
                ];
            }
        }

        return $dupes;
    }

    protected function calculatorsWithoutFaqs(): int
    {
        return (int) Calculator::query()
            ->where('is_active', true)
            ->whereDoesntHave('faqs')
            ->count();
    }
}
