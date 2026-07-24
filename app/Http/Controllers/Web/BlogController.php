<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\Seo\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function __construct(protected SeoService $seo)
    {
    }

    public function index(Request $request): View
    {
        // Include scheduled QR guides (related_qr_type) so the blog index lists every QR guide.
        $query = BlogPost::query()
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->where(function ($q) {
                $q->where('published_at', '<=', now())
                    ->orWhereNotNull('related_qr_type');
            })
            ->with(['category', 'author']);

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $posts = $query->latest('published_at')->paginate(12)->withQueryString();

        $featured = BlogPost::query()
            ->where('status', BlogPost::STATUS_PUBLISHED)
            ->where('published_at', '<=', now())
            ->featured()
            ->latest('published_at')
            ->first();

        $categories = BlogCategory::query()->active()->orderBy('name')->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Blog — CalchubNepal',
            'description' => 'Guides for calculators and QR codes — WiFi, WhatsApp, eSewa, maps, bank details and more.',
            'canonical' => route('blog.index'),
        ]);

        return view('blog.index', [
            'posts' => $posts,
            'featured' => $featured,
            'categories' => $categories,
            'meta' => $meta,
        ]);
    }

    public function show(BlogPost $post): View
    {
        $visible = $post->status === BlogPost::STATUS_PUBLISHED
            && $post->published_at !== null
            && ($post->published_at->lte(now()) || filled($post->related_qr_type));

        abort_unless($visible, 404);

        $post->incrementViews();

        $post->load(['category', 'author', 'tags', 'calculators']);

        $related = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        [$contentHtml, $toc] = $this->injectHeadingIdsAndBuildToc((string) $post->content);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Blog', 'url' => route('blog.index')],
            ['name' => $post->title, 'url' => url()->current()],
        ];

        $meta = $this->seo->buildMeta(null, [
            'title' => $post->meta_title ?: $post->title.' — AI Calculator Hub',
            'description' => $post->meta_description ?: $post->excerpt,
            'og_image' => $post->featured_image,
            'canonical' => route('blog.show', $post),
        ]);

        return view('blog.show', [
            'post' => $post,
            'contentHtml' => $contentHtml,
            'related' => $related,
            'toc' => $toc,
            'breadcrumbs' => $breadcrumbs,
            'meta' => $meta,
            'breadcrumbSchema' => $this->seo->breadcrumbSchema($breadcrumbs),
        ]);
    }

    /**
     * Ensure h2/h3 headings have unique ids and build a matching TOC.
     *
     * @return array{0: string, 1: array<int, array{id: string, text: string, level: int}>}
     */
    protected function injectHeadingIdsAndBuildToc(string $html): array
    {
        if ($html === '') {
            return ['', []];
        }

        $toc = [];
        $used = [];

        $content = preg_replace_callback(
            '/<h([23])(\s[^>]*)?>(.*?)<\/h\1>/is',
            function (array $match) use (&$toc, &$used): string {
                $level = (int) $match[1];
                $attrs = $match[2] ?? '';
                $inner = $match[3];
                $text = trim(strip_tags($inner));

                if ($text === '') {
                    return $match[0];
                }

                if (preg_match('/\bid\s*=\s*(["\'])(.*?)\1/i', $attrs, $idMatch)) {
                    $id = $idMatch[2];
                } else {
                    $base = \Illuminate\Support\Str::slug($text) ?: 'section';
                    $id = $base;
                    $n = 2;
                    while (isset($used[$id])) {
                        $id = $base.'-'.$n;
                        $n++;
                    }
                    $attrs .= ' id="'.e($id).'"';
                }

                $used[$id] = true;
                $toc[] = [
                    'id' => $id,
                    'text' => $text,
                    'level' => $level,
                ];

                return '<h'.$level.$attrs.'>'.$inner.'</h'.$level.'>';
            },
            $html
        );

        return [$content ?? $html, $toc];
    }
}
