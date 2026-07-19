<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SeoPageRequest;
use App\Models\SeoPage;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SeoPageController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        return view('admin.seo-pages.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = SeoPage::query();

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['title', 'slug'],
            orderableColumns: ['title', 'slug', 'is_active', 'created_at'],
            transform: function (SeoPage $page) {
                return [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'is_active' => (bool) $page->is_active,
                    'created_at' => $page->created_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(SeoPageRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_by'] = $request->user()?->id;

        $page = SeoPage::create($data);

        $this->activityLog->log('create', 'seo_pages', $page, ['title' => $page->title]);

        return response()->json(['message' => 'SEO page created successfully.', 'data' => $page], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => SeoPage::findOrFail($id)]);
    }

    public function update(SeoPageRequest $request, int $id): JsonResponse
    {
        $page = SeoPage::findOrFail($id);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['updated_by'] = $request->user()?->id;

        $page->update($data);

        $this->activityLog->log('update', 'seo_pages', $page, ['title' => $page->title]);

        return response()->json(['message' => 'SEO page updated successfully.', 'data' => $page]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $page = SeoPage::findOrFail($id);
        $title = $page->title;
        $page->update(['deleted_by' => $request->user()?->id]);
        $page->delete();

        $this->activityLog->log('delete', 'seo_pages', null, ['title' => $title]);

        return response()->json(['message' => 'SEO page deleted successfully.']);
    }
}
