<?php

namespace App\Services\Seo;

use App\Models\SeoPage;
use App\Services\Settings\AppSettings;

/**
 * Builds page meta arrays and schema.org structured data for calculator,
 * FAQ, and breadcrumb rich results.
 */
class SeoService
{
    public function __construct(protected AppSettings $hub)
    {
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function buildMeta(?SeoPage $page = null, array $overrides = []): array
    {
        $defaults = [
            'title' => $this->hub->defaultMetaTitle(),
            'description' => $this->hub->defaultMetaDescription(),
            'keywords' => null,
            'canonical' => $this->normalizeCanonical(url()->current()),
            'og_image' => null,
            'robots' => 'index,follow',
        ];

        $fromPage = $page ? array_filter([
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description,
            'keywords' => $page->meta_keywords,
            'canonical' => $page->canonical_url ?: $this->normalizeCanonical(url()->current()),
            'og_image' => $page->og_image,
            'robots' => $page->robots ?: 'index,follow',
        ], static fn ($value) => $value !== null) : [];

        $meta = array_merge($defaults, $fromPage, $overrides);

        if (! empty($meta['canonical'])) {
            $meta['canonical'] = $this->normalizeCanonical((string) $meta['canonical']);
        }

        if (! empty($meta['og_image'])) {
            $meta['og_image'] = $this->normalizeCanonical((string) $meta['og_image'], keepPathAsIs: true);
        }

        return $meta;
    }

    /**
     * Rewrite any absolute/relative URL onto APP_URL host + https.
     * Strips query strings and fragments so ?template= / ?utm= do not create duplicates.
     */
    public function normalizeCanonical(string $url, bool $keepPathAsIs = false): string
    {
        $root = rtrim((string) config('app.url'), '/');
        $rootParts = parse_url($root) ?: [];

        if ($url === '' || $url === '/') {
            return $root.'/';
        }

        // Relative path
        if (str_starts_with($url, '/')) {
            return $root.$url;
        }

        $parts = parse_url($url) ?: [];
        $path = $parts['path'] ?? '/';

        if ($keepPathAsIs && isset($parts['host']) && isset($rootParts['host'])
            && strcasecmp((string) $parts['host'], (string) $rootParts['host']) === 0) {
            return $url;
        }

        // Asset URLs (storage/images) — keep full path under preferred host
        $scheme = $rootParts['scheme'] ?? 'https';
        $host = $rootParts['host'] ?? ($parts['host'] ?? 'localhost');

        return $scheme.'://'.$host.$path;
    }

    /**
     * Schema.org `WebApplication` structured data for a calculator page.
     *
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function calculatorSchema(string $name, ?string $description, string $url, array $overrides = []): array
    {
        return array_merge([
            '@context' => 'https://schema.org',
            '@type' => 'WebApplication',
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem' => 'Any',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => 'USD',
            ],
        ], $overrides);
    }

    /**
     * Schema.org `FAQPage` structured data.
     *
     * @param  array<int, array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>
     */
    public function faqSchema(array $faqs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(static fn (array $faq) => [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ], $faqs),
        ];
    }

    /**
     * Schema.org `BreadcrumbList` structured data.
     *
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                static fn (array $item, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $item['url'],
                ],
                $items,
                array_keys($items)
            )),
        ];
    }
}
