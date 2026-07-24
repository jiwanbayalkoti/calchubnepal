<?php

namespace App\Services\Seo;

use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class PublicSitemapService
{
    /**
     * Static marketing / tool pages for XML + HTML sitemaps.
     *
     * @return list<array{label: string, route: string, priority: float, changefreq: string, group: string}>
     */
    public function staticPages(): array
    {
        return [
            ['label' => 'Home', 'route' => 'home', 'priority' => 1.0, 'changefreq' => Url::CHANGE_FREQUENCY_DAILY, 'group' => 'main'],
            ['label' => 'All Calculators', 'route' => 'calculators.index', 'priority' => 0.9, 'changefreq' => Url::CHANGE_FREQUENCY_DAILY, 'group' => 'main'],
            ['label' => 'Categories', 'route' => 'categories.index', 'priority' => 0.7, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'group' => 'main'],
            ['label' => 'Blog', 'route' => 'blog.index', 'priority' => 0.6, 'changefreq' => Url::CHANGE_FREQUENCY_DAILY, 'group' => 'main'],
            ['label' => 'Search', 'route' => 'search.results', 'priority' => 0.4, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'group' => 'main'],
            ['label' => 'Pricing', 'route' => 'pricing', 'priority' => 0.5, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'group' => 'main'],
            ['label' => 'About Us', 'route' => 'about', 'priority' => 0.4, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'group' => 'main'],
            ['label' => 'Contact', 'route' => 'contact', 'priority' => 0.4, 'changefreq' => Url::CHANGE_FREQUENCY_MONTHLY, 'group' => 'main'],
            ['label' => 'HTML Sitemap', 'route' => 'sitemap', 'priority' => 0.3, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'group' => 'main'],

            ['label' => 'QR Code Generator', 'route' => 'qr-code-generator', 'priority' => 0.85, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'group' => 'tools'],
            ['label' => 'Visiting Card Designer', 'route' => 'visiting-card-designer', 'priority' => 0.85, 'changefreq' => Url::CHANGE_FREQUENCY_WEEKLY, 'group' => 'tools'],

            ['label' => 'Privacy Policy', 'route' => 'privacy', 'priority' => 0.2, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'group' => 'legal'],
            ['label' => 'Terms & Conditions', 'route' => 'terms', 'priority' => 0.2, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'group' => 'legal'],
            ['label' => 'Cookie Policy', 'route' => 'cookies', 'priority' => 0.2, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'group' => 'legal'],
            ['label' => 'Disclaimer', 'route' => 'disclaimer', 'priority' => 0.2, 'changefreq' => Url::CHANGE_FREQUENCY_YEARLY, 'group' => 'legal'],
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    public function linksForGroup(string $group): array
    {
        return collect($this->staticPages())
            ->where('group', $group)
            ->map(fn (array $page) => [
                'label' => $page['label'],
                'url' => route($page['route']),
            ])
            ->values()
            ->all();
    }

    /**
     * Auth / account entry points (HTML only — not in XML).
     *
     * @return list<array{label: string, url: string}>
     */
    public function accountLinks(): array
    {
        return [
            ['label' => 'Login', 'url' => route('login')],
            ['label' => 'Register', 'url' => route('register')],
            ['label' => 'XML Sitemap', 'url' => route('sitemap.xml')],
        ];
    }

    public function renderXml(): string
    {
        $sitemap = Sitemap::create();
        $now = Carbon::now();

        foreach ($this->staticPages() as $page) {
            $sitemap->add(
                Url::create(route($page['route']))
                    ->setPriority($page['priority'])
                    ->setChangeFrequency($page['changefreq'])
                    ->setLastModificationDate($now)
            );
        }

        CalculatorCategory::query()->active()->ordered()->each(function (CalculatorCategory $category) use ($sitemap) {
            $url = Url::create(route('categories.show', $category))
                ->setPriority(0.7)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
            if ($category->updated_at) {
                $url->setLastModificationDate($category->updated_at);
            }
            $sitemap->add($url);
        });

        Calculator::query()->active()->each(function (Calculator $calculator) use ($sitemap) {
            $url = Url::create(route('calculators.show', $calculator))
                ->setPriority(0.8)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY);
            if ($calculator->updated_at) {
                $url->setLastModificationDate($calculator->updated_at);
            }
            $sitemap->add($url);
        });

        BlogPost::query()->published()->each(function (BlogPost $post) use ($sitemap) {
            $url = Url::create(route('blog.show', $post))
                ->setPriority(0.55)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY);
            $lastMod = $post->updated_at ?? $post->published_at;
            if ($lastMod) {
                $url->setLastModificationDate($lastMod);
            }
            $sitemap->add($url);
        });

        return $sitemap->render();
    }

    public function cacheKey(): string
    {
        return 'calc_hub:sitemap:xml:v3';
    }
}
