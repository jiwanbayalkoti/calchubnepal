<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\CalculatorCategory;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        return view('admin.categories.index');
    }

    public function data(Request $request): JsonResponse
    {
        $query = CalculatorCategory::query()->withCount('calculators');

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'slug'],
            orderableColumns: ['name', 'sort_order', 'is_active', 'calculators_count', 'created_at'],
            transform: function (CalculatorCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'calculators_count' => $category->calculators_count,
                    'sort_order' => $category->sort_order,
                    'is_active' => (bool) $category->is_active,
                ];
            }
        );
    }

    public function store(CategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_by'] = $request->user()?->id;

        $category = CalculatorCategory::create($data);

        $this->activityLog->log('create', 'categories', $category, ['name' => $category->name]);

        return response()->json(['message' => 'Category created successfully.', 'data' => $category], 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = CalculatorCategory::findOrFail($id);

        return response()->json(['data' => $category]);
    }

    public function update(CategoryRequest $request, int $id): JsonResponse
    {
        $category = CalculatorCategory::findOrFail($id);

        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['updated_by'] = $request->user()?->id;

        $category->update($data);

        $this->activityLog->log('update', 'categories', $category, ['name' => $category->name]);

        return response()->json(['message' => 'Category updated successfully.', 'data' => $category]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $category = CalculatorCategory::findOrFail($id);

        if ($category->calculators()->exists()) {
            return response()->json([
                'message' => 'This category still has calculators assigned and cannot be deleted.',
            ], 422);
        }

        $category->update(['deleted_by' => $request->user()?->id]);
        $category->delete();

        $this->activityLog->log('delete', 'categories', null, ['name' => $category->name]);

        return response()->json(['message' => 'Category deleted successfully.']);
    }
}
