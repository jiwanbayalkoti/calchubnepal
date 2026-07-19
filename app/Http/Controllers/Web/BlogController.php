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
        $query = BlogPost::query()->published()->with(['category', 'author']);

        if ($categorySlug = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        $posts = $query->latest('published_at')->paginate(9)->withQueryString();

        $featured = BlogPost::query()->published()->featured()->latest('published_at')->first();

        $categories = BlogCategory::query()->active()->orderBy('name')->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Blog — AI Calculator Hub',
            'description' => 'Guides, tips and explainers on finance, health, construction and everyday math from the AI Calculator Hub team.',
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
        abort_unless($post->isPublished(), 404);

        $post->incrementViews();

        $post->load(['category', 'author', 'tags', 'calculators']);

        $related = BlogPost::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->when($post->blog_category_id, fn ($q) => $q->where('blog_category_id', $post->blog_category_id))
            ->latest('published_at')
            ->take(3)
            ->get();

        $toc = $this->buildTableOfContents((string) $post->content);

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
            'related' => $related,
            'toc' => $toc,
            'breadcrumbs' => $breadcrumbs,
            'meta' => $meta,
            'breadcrumbSchema' => $this->seo->breadcrumbSchema($breadcrumbs),
        ]);
    }

    /**
     * Extract h2/h3 headings from HTML content to build a simple TOC.
     *
     * @return array<int, array{id: string, text: string, level: int}>
     */
    protected function buildTableOfContents(string $html): array
    {
        if ($html === '') {
            return [];
        }

        $toc = [];

        if (preg_match_all('/<h([23])[^>]*>(.*?)<\/h\1>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $text = trim(strip_tags($match[2]));

                if ($text === '') {
                    continue;
                }

                $toc[] = [
                    'id' => \Illuminate\Support\Str::slug($text),
                    'text' => $text,
                    'level' => (int) $match[1],
                ];
            }
        }

        return $toc;
    }
}
