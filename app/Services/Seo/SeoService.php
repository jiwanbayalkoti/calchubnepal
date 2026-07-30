<?php

namespace App\Services\Seo;

use App\Models\SeoPage;
use App\Services\Settings\AppSettings;
use Illuminate\Support\Str;

/**
 * Builds page meta arrays and schema.org JSON-LD for public pages.
 * All defaults and templates resolve from Admin → Settings (seo group).
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
            'keywords' => $this->hub->defaultMetaKeywords(),
            'canonical' => $this->normalizeCanonical(url()->current()),
            'og_image' => $this->hub->defaultOgImage(),
            'og_type' => 'website',
            'robots' => 'index,follow',
            'author' => $this->hub->publisherName(),
            'publisher' => $this->hub->publisherName(),
            'language' => str_replace('_', '-', app()->getLocale()),
            'theme_color' => $this->hub->themeColor(),
            'twitter_site' => $this->hub->twitterHandle(),
        ];

        $fromPage = $page ? array_filter([
            'title' => $page->meta_title ?: $page->title,
            'description' => $page->meta_description,
            'keywords' => $page->meta_keywords,
            'canonical' => $page->canonical_url ?: $this->normalizeCanonical(url()->current()),
            'og_image' => $page->og_image,
            'robots' => $page->robots ?: 'index,follow',
        ], static fn ($value) => $value !== null && $value !== '') : [];

        $meta = array_merge($defaults, $fromPage, array_filter($overrides, static fn ($v) => $v !== null));

        if (! empty($meta['canonical'])) {
            $meta['canonical'] = $this->normalizeCanonical((string) $meta['canonical']);
        }

        if (! empty($meta['og_image']) && ! str_starts_with((string) $meta['og_image'], 'http') && ! str_starts_with((string) $meta['og_image'], '/')) {
            $meta['og_image'] = asset('storage/'.ltrim((string) $meta['og_image'], '/'));
        }

        return $meta;
    }

    /**
     * Apply an admin meta template with placeholders.
     *
     * Supported: {title}, {site}, {category}, {description}, {keywords}
     *
     * @param  array<string, string|null>  $vars
     */
    public function applyTemplate(string $templateKey, array $vars, ?string $fallback = null): string
    {
        $template = $this->hub->metaTemplate($templateKey);
        if ($template === null || $template === '') {
            return $fallback ?? ($vars['title'] ?? $this->hub->defaultMetaTitle());
        }

        $replace = [
            '{title}' => (string) ($vars['title'] ?? ''),
            '{site}' => $this->hub->siteName(),
            '{category}' => (string) ($vars['category'] ?? ''),
            '{description}' => Str::limit(strip_tags((string) ($vars['description'] ?? '')), 120, ''),
            '{keywords}' => (string) ($vars['keywords'] ?? ''),
        ];

        $result = str_replace(array_keys($replace), array_values($replace), $template);

        return trim(preg_replace('/\s+/', ' ', $result) ?? $result) ?: ($fallback ?? $this->hub->defaultMetaTitle());
    }

    /**
     * Force canonical onto APP_URL host/scheme; strip query strings.
     */
    public function normalizeCanonical(string $url): string
    {
        $root = rtrim((string) config('app.url'), '/');
        $rootParts = parse_url($root) ?: [];

        if ($url === '' || $url === '/') {
            return $root.'/';
        }

        if (str_starts_with($url, '/')) {
            return $root.$url;
        }

        $parts = parse_url($url) ?: [];
        $path = $parts['path'] ?? '/';
        $scheme = $rootParts['scheme'] ?? 'https';
        $host = $rootParts['host'] ?? ($parts['host'] ?? 'localhost');

        return $scheme.'://'.$host.$path;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public function calculatorSchema(string $name, ?string $description, string $url, array $overrides = []): array
    {
        return array_merge([
            '@context' => 'https://schema.org',
            '@type' => ['WebApplication', 'SoftwareApplication'],
            'name' => $name,
            'description' => $description,
            'url' => $this->normalizeCanonical($url),
            'applicationCategory' => 'UtilitiesApplication',
            'operatingSystem' => 'Any',
            'browserRequirements' => 'Requires JavaScript',
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => $this->hub->schemaCurrency(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->hub->publisherName(),
                'url' => rtrim((string) config('app.url'), '/'),
            ],
        ], $overrides);
    }

    /**
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
                    'text' => strip_tags($faq['answer']),
                ],
            ], $faqs),
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbSchema(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_values(array_map(
                fn (array $item, int $index) => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'item' => $this->normalizeCanonical($item['url']),
                ],
                $items,
                array_keys($items)
            )),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function organizationSchema(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->hub->publisherName(),
            'url' => rtrim((string) config('app.url'), '/'),
            'description' => $this->hub->defaultMetaDescription(),
            'email' => $this->hub->supportEmail(),
        ];

        if ($logo = $this->hub->logoUrl()) {
            $schema['logo'] = str_starts_with($logo, 'http') ? $logo : url($logo);
        }

        $sameAs = array_values($this->hub->socialLinks());
        if ($sameAs !== []) {
            $schema['sameAs'] = $sameAs;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteSchema(): array
    {
        $url = rtrim((string) config('app.url'), '/');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->hub->siteName(),
            'url' => $url,
            'description' => $this->hub->defaultMetaDescription(),
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->hub->publisherName(),
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $url.'/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function webPageSchema(string $name, string $description, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $name,
            'description' => $description,
            'url' => $this->normalizeCanonical($url),
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $this->hub->siteName(),
                'url' => rtrim((string) config('app.url'), '/'),
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function collectionPageSchema(string $name, string $description, string $url, array $items = []): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $name,
            'description' => $description,
            'url' => $this->normalizeCanonical($url),
        ];

        if ($items !== []) {
            $schema['mainEntity'] = [
                '@type' => 'ItemList',
                'itemListElement' => array_values(array_map(
                    fn (array $item, int $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $item['name'],
                        'url' => $this->normalizeCanonical($item['url']),
                    ],
                    $items,
                    array_keys($items)
                )),
            ];
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function articleSchema(
        string $headline,
        string $description,
        string $url,
        ?string $image = null,
        ?string $datePublished = null,
        ?string $dateModified = null,
        ?string $authorName = null,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $headline,
            'description' => $description,
            'url' => $this->normalizeCanonical($url),
            'mainEntityOfPage' => $this->normalizeCanonical($url),
            'author' => $this->personSchema($authorName ?: $this->hub->publisherName()),
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->hub->publisherName(),
                'url' => rtrim((string) config('app.url'), '/'),
            ],
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ];

        if ($image) {
            $schema['image'] = [str_starts_with($image, 'http') ? $image : url($image)];
        }
        if ($datePublished) {
            $schema['datePublished'] = $datePublished;
        }
        if ($dateModified) {
            $schema['dateModified'] = $dateModified;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    public function personSchema(string $name, ?string $url = null): array
    {
        $schema = [
            '@type' => 'Person',
            'name' => $name,
        ];
        if ($url) {
            $schema['url'] = $this->normalizeCanonical($url);
        }

        return $schema;
    }

    /**
     * @param  array<int, array{name: string, text: string}>  $steps
     * @return array<string, mixed>
     */
    public function howToSchema(string $name, string $description, array $steps): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $name,
            'description' => $description,
            'step' => array_values(array_map(
                static fn (array $step, int $i) => [
                    '@type' => 'HowToStep',
                    'position' => $i + 1,
                    'name' => $step['name'],
                    'text' => $step['text'],
                ],
                $steps,
                array_keys($steps)
            )),
        ];
    }

    /**
     * LocalBusiness schema when NAP data is configured in admin.
     *
     * @return array<string, mixed>|null
     */
    public function localBusinessSchema(): ?array
    {
        if (! $this->hub->localBusinessEnabled()) {
            return null;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $this->hub->publisherName(),
            'url' => rtrim((string) config('app.url'), '/'),
            'email' => $this->hub->supportEmail(),
            'description' => $this->hub->defaultMetaDescription(),
        ];

        if ($phone = $this->hub->businessPhone()) {
            $schema['telephone'] = $phone;
        }
        if ($address = $this->hub->businessAddress()) {
            $schema['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressCountry' => $this->hub->businessCountry(),
            ];
        }

        return $schema;
    }

    /**
     * Global schemas for every public HTML page (Organization + WebSite + optional LocalBusiness).
     *
     * @return list<array<string, mixed>>
     */
    public function globalSchemas(): array
    {
        $schemas = [
            $this->organizationSchema(),
            $this->websiteSchema(),
        ];

        if ($local = $this->localBusinessSchema()) {
            $schemas[] = $local;
        }

        return $schemas;
    }
}
