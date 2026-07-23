<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogPostController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        $this->authorize('viewAny', BlogPost::class);

        $categories = BlogCategory::query()->active()->get(['id', 'name']);

        return view('admin.blog-posts.index', compact('categories'));
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BlogPost::class);

        $query = BlogPost::query()->with(['category', 'author']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['title', 'slug'],
            orderableColumns: ['title', 'blog_category_id', 'status', 'views_count', 'published_at', 'created_at'],
            transform: function (BlogPost $post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'category' => $post->category?->name,
                    'author' => $post->author?->name,
                    'status' => $post->status,
                    'is_featured' => (bool) $post->is_featured,
                    'views_count' => $post->views_count,
                    'published_at' => $post->published_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(BlogPostRequest $request): JsonResponse
    {
        $this->authorize('create', BlogPost::class);

        $data = $request->safe()->except('tags');
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['user_id'] = $request->user()?->id;
        $data['created_by'] = $request->user()?->id;

        $post = BlogPost::create($data);
        $this->syncTags($post, $request->input('tags', []));

        $this->activityLog->log('create', 'blog_posts', $post, ['title' => $post->title]);

        return response()->json(['message' => 'Blog post created successfully.', 'data' => $post], 201);
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::with('tags')->findOrFail($id);

        $this->authorize('view', $post);

        $data = $post->toArray();
        $data['tags'] = $post->tags->pluck('name')->implode(', ');
        // datetime-local needs local (app timezone) Y-m-d\TH:i, not ISO8601 UTC
        $data['published_at'] = $post->published_at?->timezone(config('app.timezone'))->format('Y-m-d\TH:i');

        return response()->json(['data' => $data]);
    }

    public function update(BlogPostRequest $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $this->authorize('update', $post);

        $data = $request->safe()->except('tags');
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['updated_by'] = $request->user()?->id;

        $post->update($data);
        $this->syncTags($post, $request->input('tags', []));

        $this->activityLog->log('update', 'blog_posts', $post, ['title' => $post->title]);

        return response()->json(['message' => 'Blog post updated successfully.', 'data' => $post]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $this->authorize('delete', $post);

        $title = $post->title;
        $post->update(['deleted_by' => $request->user()?->id]);
        $post->delete();

        $this->activityLog->log('delete', 'blog_posts', null, ['title' => $title]);

        return response()->json(['message' => 'Blog post deleted successfully.']);
    }

    /**
     * @param  array<int, string>|string  $tagNames
     */
    protected function syncTags(BlogPost $post, array|string $tagNames): void
    {
        if (is_string($tagNames)) {
            $tagNames = array_filter(array_map('trim', explode(',', $tagNames)));
        }

        $tagIds = collect($tagNames)->filter()->map(function (string $name) {
            return BlogTag::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])->id;
        });

        $post->tags()->sync($tagIds);
    }
}
