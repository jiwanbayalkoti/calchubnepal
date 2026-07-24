<?php

namespace App\Services\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Models\User;
use App\Services\Ai\AiServiceInterface;
use App\Services\Settings\AppSettings;
use DomainException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

/**
 * Builds SEO blog posts from admin instructions via the AI service layer.
 * Controllers must call this service — never AI providers directly.
 */
class AiBlogGeneratorService
{
    public const PROMPT_SLUG = 'generate-blog';

    public function __construct(
        protected AiServiceInterface $ai,
        protected AppSettings $hub,
    ) {
    }

    /**
     * Generate blog fields from admin instructions (does not persist).
     *
     * @param  array{
     *     instructions: string,
     *     title_hint?: string|null,
     *     keyword?: string|null,
     *     language?: string|null,
     *     tone?: string|null,
     *     word_count?: int|null,
     *     audience?: string|null,
     *     category_id?: int|null,
     *     calculator_title?: string|null,
     * }  $input
     * @return array{
     *     title: string,
     *     slug: string,
     *     excerpt: string,
     *     content: string,
     *     tags: array<int, string>,
     *     meta_title: string,
     *     meta_description: string,
     *     meta_keywords: string,
     *     blog_category_id: int|null,
     *     tokens_used: int|null
     * }
     */
    public function generate(array $input, ?User $user = null): array
    {
        if (! $this->hub->aiEnabled()) {
            throw new DomainException('AI is disabled in site settings. Enable it under Settings → AI.');
        }

        $instructions = trim((string) ($input['instructions'] ?? ''));
        if ($instructions === '') {
            throw new InvalidArgumentException('Admin instructions are required.');
        }

        $category = null;
        if (! empty($input['category_id'])) {
            $category = BlogCategory::query()->find((int) $input['category_id']);
        }

        $wordCount = max(400, min(2500, (int) ($input['word_count'] ?? 900)));
        // Gemini Flash "thinking" can burn 1.5k–3k tokens; keep headroom for full JSON.
        $maxTokens = max(8192, (int) ceil($wordCount * 3.5) + 4000);

        $variables = [
            'instructions' => $instructions,
            'title_hint' => trim((string) ($input['title_hint'] ?? '')) ?: 'Decide a clear SEO title from the instructions.',
            'keyword' => trim((string) ($input['keyword'] ?? '')) ?: 'Infer the best primary keyword from the instructions.',
            'language' => $this->normalizeLanguage($input['language'] ?? 'en'),
            'tone' => trim((string) ($input['tone'] ?? 'clear, helpful, professional')) ?: 'clear, helpful, professional',
            'word_count' => (string) $wordCount,
            'min_words' => (string) max(350, (int) floor($wordCount * 0.9)),
            'audience' => trim((string) ($input['audience'] ?? 'general readers in Nepal and South Asia')) ?: 'general readers',
            'category_name' => $category?->name ?? 'General',
            'calculator_title' => trim((string) ($input['calculator_title'] ?? '')) ?: 'CalchubNepal calculators / QR tools when relevant',
            'site_name' => $this->hub->siteName(),
        ];

        try {
            $result = $this->ai->generateFromPrompt(
                self::PROMPT_SLUG,
                $variables,
                [
                    'max_tokens' => $maxTokens,
                    'disable_thinking' => true,
                ],
                $user
            );
        } catch (Throwable $e) {
            throw new DomainException('AI blog generation failed: '.$e->getMessage(), 0, $e);
        }

        $parsed = $this->parseStructuredContent((string) ($result['content'] ?? ''));
        $parsed['content'] = $this->cleanupContentArtifacts($parsed['content']);

        $title = $parsed['title'] !== '' ? $parsed['title'] : Str::limit($instructions, 80, '');
        $slug = Str::slug($title) ?: 'ai-blog-'.now()->format('YmdHis');

        return [
            'title' => $this->cleanupContentArtifacts($title),
            'slug' => $this->uniqueSlug($slug),
            'excerpt' => $this->cleanupContentArtifacts($parsed['excerpt']),
            'content' => $parsed['content'],
            'tags' => $parsed['tags'],
            'meta_title' => $parsed['meta_title'] !== '' ? $parsed['meta_title'] : Str::limit($title, 60, ''),
            'meta_description' => $parsed['meta_description'] !== '' ? $parsed['meta_description'] : Str::limit(strip_tags($parsed['excerpt'] ?: $parsed['content']), 160, ''),
            'meta_keywords' => $parsed['meta_keywords'],
            'blog_category_id' => $category?->id,
            'tokens_used' => $result['tokens_used'] ?? null,
        ];
    }

    /**
     * Generate and persist a blog post (draft or published).
     *
     * @param  array<string, mixed>  $input
     * @return array{post: BlogPost, generated: array<string, mixed>}
     */
    public function generateAndSave(array $input, string $status, ?User $user = null): array
    {
        $generated = $this->generate($input, $user);

        $status = in_array($status, [BlogPost::STATUS_DRAFT, BlogPost::STATUS_PUBLISHED], true)
            ? $status
            : BlogPost::STATUS_DRAFT;

        $publishedAt = null;
        if ($status === BlogPost::STATUS_PUBLISHED) {
            $publishedAt = ! empty($input['published_at'])
                ? $input['published_at']
                : now();
        }

        $post = BlogPost::create([
            'blog_category_id' => $generated['blog_category_id'],
            'user_id' => $user?->id,
            'title' => $generated['title'],
            'slug' => $generated['slug'],
            'excerpt' => $generated['excerpt'],
            'content' => $generated['content'],
            'meta_title' => $generated['meta_title'],
            'meta_description' => $generated['meta_description'],
            'meta_keywords' => $generated['meta_keywords'],
            'status' => $status,
            'published_at' => $publishedAt,
            'ai_generated' => true,
            'created_by' => $user?->id,
        ]);

        $this->syncTags($post, $generated['tags']);

        return [
            'post' => $post->fresh(['category', 'tags', 'author']),
            'generated' => $generated,
        ];
    }

    /**
     * @return array{
     *     title: string,
     *     excerpt: string,
     *     content: string,
     *     tags: array<int, string>,
     *     meta_title: string,
     *     meta_description: string,
     *     meta_keywords: string
     * }
     */
    protected function parseStructuredContent(string $raw): array
    {
        $raw = $this->stripCodeFences(trim($raw));
        $json = $this->extractJson($raw);

        if ($json === null) {
            $json = $this->salvagePartialJson($raw);
        }

        if ($json === null) {
            // Avoid rendering raw JSON braces as paragraphs.
            if ($this->looksLikeJson($raw)) {
                $salvagedContent = $this->extractJsonStringField($raw, 'content')
                    ?? $this->extractHtmlBody($raw);

                return [
                    'title' => $this->extractJsonStringField($raw, 'title') ?? '',
                    'excerpt' => $this->extractJsonStringField($raw, 'excerpt') ?? '',
                    'content' => $this->normalizeHtml($salvagedContent ?: ''),
                    'tags' => $this->extractJsonTags($raw),
                    'meta_title' => $this->extractJsonStringField($raw, 'meta_title') ?? '',
                    'meta_description' => $this->extractJsonStringField($raw, 'meta_description') ?? '',
                    'meta_keywords' => $this->extractJsonStringField($raw, 'meta_keywords') ?? '',
                ];
            }

            $html = $this->normalizeHtml($raw);

            return [
                'title' => '',
                'excerpt' => Str::limit(strip_tags($html), 220, ''),
                'content' => $html,
                'tags' => [],
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => '',
            ];
        }

        $tags = $json['tags'] ?? [];
        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }
        if (! is_array($tags)) {
            $tags = [];
        }

        $content = (string) ($json['content'] ?? $json['body'] ?? '');
        $content = $this->normalizeHtml($content);

        return [
            'title' => trim((string) ($json['title'] ?? '')),
            'excerpt' => trim((string) ($json['excerpt'] ?? '')),
            'content' => $content,
            'tags' => array_values(array_filter(array_map(
                static fn ($t) => trim((string) $t),
                $tags
            ))),
            'meta_title' => trim((string) ($json['meta_title'] ?? '')),
            'meta_description' => trim((string) ($json['meta_description'] ?? '')),
            'meta_keywords' => is_array($json['meta_keywords'] ?? null)
                ? implode(', ', $json['meta_keywords'])
                : trim((string) ($json['meta_keywords'] ?? '')),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function extractJson(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Largest JSON-looking object.
        if (preg_match('/\{[\s\S]*\}/', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    /**
     * Repair truncated JSON from MAX_TOKENS cutoffs so title/excerpt/content still parse.
     *
     * @return array<string, mixed>|null
     */
    protected function salvagePartialJson(string $raw): ?array
    {
        if (! $this->looksLikeJson($raw)) {
            return null;
        }

        $title = $this->extractJsonStringField($raw, 'title');
        $excerpt = $this->extractJsonStringField($raw, 'excerpt');
        $content = $this->extractJsonStringField($raw, 'content', allowUnclosed: true);
        $metaTitle = $this->extractJsonStringField($raw, 'meta_title');
        $metaDescription = $this->extractJsonStringField($raw, 'meta_description');
        $metaKeywords = $this->extractJsonStringField($raw, 'meta_keywords');

        if ($title === null && $content === null) {
            return null;
        }

        return [
            'title' => $title ?? '',
            'excerpt' => $excerpt ?? '',
            'content' => $content ?? '',
            'tags' => $this->extractJsonTags($raw),
            'meta_title' => $metaTitle ?? '',
            'meta_description' => $metaDescription ?? '',
            'meta_keywords' => $metaKeywords ?? '',
        ];
    }

    protected function extractJsonStringField(string $raw, string $field, bool $allowUnclosed = false): ?string
    {
        $pattern = '/"'.preg_quote($field, '/').'"\s*:\s*"/';
        if (! preg_match($pattern, $raw, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $start = $m[0][1] + strlen($m[0][0]);
        $out = '';
        $len = strlen($raw);

        for ($i = $start; $i < $len; $i++) {
            $ch = $raw[$i];
            if ($ch === '\\' && $i + 1 < $len) {
                $next = $raw[$i + 1];
                $map = [
                    '"' => '"',
                    '\\' => '\\',
                    '/' => '/',
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                ];
                $out .= $map[$next] ?? $next;
                $i++;
                continue;
            }
            if ($ch === '"') {
                return $out;
            }
            $out .= $ch;
        }

        return $allowUnclosed ? $out : null;
    }

    /**
     * @return array<int, string>
     */
    protected function extractJsonTags(string $raw): array
    {
        if (! preg_match('/"tags"\s*:\s*\[(.*?)\]/s', $raw, $m)) {
            return [];
        }

        preg_match_all('/"((?:\\\\.|[^"\\\\])*)"/', $m[1], $tags);

        return array_values(array_filter(array_map(
            static fn ($t) => trim(stripcslashes($t)),
            $tags[1] ?? []
        )));
    }

    protected function extractHtmlBody(string $raw): string
    {
        if (preg_match_all('/<(?:p|h[2-4]|ul|ol|li)\b[\s\S]*?<\/(?:p|h[2-4]|ul|ol|li)>/i', $raw, $m)) {
            return implode("\n", $m[0]);
        }

        return '';
    }

    protected function looksLikeJson(string $raw): bool
    {
        $trim = ltrim($raw);

        return str_starts_with($trim, '{') || str_contains($raw, '"title"') || str_contains($raw, '"content"');
    }

    protected function stripCodeFences(string $raw): string
    {
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/i', $raw, $m)) {
            return trim($m[1]);
        }

        return $raw;
    }

    protected function cleanupContentArtifacts(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Remove accidental whole-string JSON braces wrappers: {paragraph}
        $text = preg_replace('/^\{\s*(<(?:p|h[1-6]|ul|ol)\b)/i', '$1', $text) ?? $text;
        $text = preg_replace('/(<\/(?:p|h[1-6]|ul|ol)>)\s*\}$/i', '$1', $text) ?? $text;

        // Remove {sentence} wrappers that models sometimes emit.
        $text = preg_replace('/\{([^{}]{3,400})\}/', '$1', $text) ?? $text;

        // Strip leftover raw JSON keys if any slipped into HTML.
        $text = preg_replace('/^\s*"(?:title|excerpt|content|tags|meta_title|meta_description|meta_keywords)"\s*:\s*/m', '', $text) ?? $text;

        return trim($text);
    }

    protected function normalizeHtml(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return '';
        }

        // Already HTML.
        if (preg_match('/<\/?(p|h[1-6]|ul|ol|li|div|br|strong|em|a)\b/i', $content)) {
            return $content;
        }

        // Lightweight Markdown → HTML for headings/paragraphs/lists.
        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];
        $html = [];
        $inList = false;

        foreach ($lines as $line) {
            $trim = trim($line);
            if ($trim === '') {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                continue;
            }

            // Skip bare JSON braces / keys so they never become paragraphs.
            if ($trim === '{' || $trim === '}' || preg_match('/^"[a-z_]+"\s*:/i', $trim)) {
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.+)$/', $trim, $m)) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $level = strlen($m[1]) + 1; // # → h2, ## → h3, ### → h4
                $level = min(4, max(2, $level));
                $html[] = '<h'.$level.'>'.e($m[2]).'</h'.$level.'>';
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $trim, $m)) {
                if (! $inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>'.e($m[1]).'</li>';
                continue;
            }

            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }

            $para = e($trim);
            $para = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $para) ?? $para;
            $para = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $para) ?? $para;
            $html[] = '<p>'.$para.'</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return implode("\n", $html);
    }

    protected function normalizeLanguage(?string $language): string
    {
        $language = strtolower(trim((string) $language));

        return match ($language) {
            'ne', 'nepali', 'np' => 'Nepali (नेपाली)',
            'en', 'english', '' => 'English',
            default => $language,
        };
    }

    protected function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (BlogPost::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    /**
     * @param  array<int, string>  $tagNames
     */
    protected function syncTags(BlogPost $post, array $tagNames): void
    {
        $tagIds = collect($tagNames)->filter()->map(function (string $name) {
            return BlogTag::firstOrCreate(
                ['slug' => Str::slug($name) ?: Str::random(6)],
                ['name' => $name]
            )->id;
        });

        $post->tags()->sync($tagIds);
    }
}
