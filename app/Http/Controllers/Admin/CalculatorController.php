<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCalculatorRequest;
use App\Http\Requests\Admin\UpdateCalculatorRequest;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Services\Activity\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(
        protected CalculatorRepositoryInterface $calculators,
        protected ActivityLogService $activityLog,
    ) {
    }

    public function index(): View
    {
        $this->authorize('viewAny', Calculator::class);

        $categories = CalculatorCategory::query()->ordered()->get(['id', 'name']);

        return view('admin.calculators.index', compact('categories'));
    }

    public function data(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Calculator::class);

        $query = Calculator::query()->with('category');

        if ($request->filled('category_id')) {
            $query->where('calculator_category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->filled('premium')) {
            $query->where('is_premium', $request->input('premium') === 'premium');
        }

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['title', 'slug', 'formula_key'],
            orderableColumns: ['title', 'calculator_category_id', 'is_premium', 'is_featured', 'is_active', 'usage_count', 'created_at'],
            transform: function (Calculator $calculator) {
                return [
                    'id' => $calculator->id,
                    'title' => $calculator->title,
                    'slug' => $calculator->slug,
                    'category' => $calculator->category?->name,
                    'is_premium' => (bool) $calculator->is_premium,
                    'is_featured' => (bool) $calculator->is_featured,
                    'is_active' => (bool) $calculator->is_active,
                    'usage_count' => $calculator->usage_count,
                    'created_at' => $calculator->created_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(StoreCalculatorRequest $request): JsonResponse
    {
        $data = $this->prepareData($request->validated());
        $data['created_by'] = $request->user()?->id;

        $calculator = $this->calculators->create($data);

        $this->activityLog->log('create', 'calculators', $calculator, ['title' => $calculator->title]);

        return response()->json([
            'message' => 'Calculator created successfully.',
            'data' => $calculator,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $calculator = $this->calculators->find($id);
        abort_if(! $calculator, 404);

        $this->authorize('view', $calculator);

        $payload = $calculator->toArray();
        $payload['input_schema'] = json_encode($calculator->input_schema, JSON_PRETTY_PRINT);
        $payload['validation_rules'] = json_encode($calculator->validation_rules, JSON_PRETTY_PRINT);
        $payload['result_schema'] = json_encode($calculator->result_schema, JSON_PRETTY_PRINT);

        return response()->json(['data' => $payload]);
    }

    public function update(UpdateCalculatorRequest $request, int $id): JsonResponse
    {
        $calculator = $this->calculators->find($id);
        abort_if(! $calculator, 404);

        $this->authorize('update', $calculator);

        $data = $this->prepareData($request->validated());
        $data['updated_by'] = $request->user()?->id;

        $calculator = $this->calculators->update($calculator, $data);

        $this->activityLog->log('update', 'calculators', $calculator, ['title' => $calculator->title]);

        return response()->json([
            'message' => 'Calculator updated successfully.',
            'data' => $calculator,
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $calculator = $this->calculators->find($id);
        abort_if(! $calculator, 404);

        $this->authorize('delete', $calculator);

        $title = $calculator->title;
        $calculator->update(['deleted_by' => $request->user()?->id]);
        $this->calculators->delete($calculator);

        $this->activityLog->log('delete', 'calculators', null, ['title' => $title]);

        return response()->json(['message' => 'Calculator deleted successfully.']);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $calculator = $this->calculators->find($id);
        abort_if(! $calculator, 404);

        $this->authorize('update', $calculator);

        $calculator = $this->calculators->update($calculator, ['is_active' => ! $calculator->is_active]);

        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $calculator,
        ]);
    }

    public function toggleFeatured(int $id): JsonResponse
    {
        $calculator = $this->calculators->find($id);
        abort_if(! $calculator, 404);

        $this->authorize('update', $calculator);

        $calculator = $this->calculators->update($calculator, ['is_featured' => ! $calculator->is_featured]);

        return response()->json([
            'message' => 'Featured flag updated successfully.',
            'data' => $calculator,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function prepareData(array $validated): array
    {
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        foreach (['input_schema', 'validation_rules', 'result_schema'] as $jsonField) {
            if (array_key_exists($jsonField, $validated) && is_string($validated[$jsonField])) {
                $validated[$jsonField] = json_decode($validated[$jsonField], true);
            }
        }

        $validated['is_premium'] = $validated['is_premium'] ?? false;
        $validated['is_featured'] = $validated['is_featured'] ?? false;
        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }
}
