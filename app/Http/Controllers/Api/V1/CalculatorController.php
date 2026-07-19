<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Contracts\Services\CalculatorServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\CalculatorCollection;
use App\Http\Resources\CalculatorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

/**
 * Public (and optionally authenticated) REST API for calculators, reusing
 * the exact same service/repository layer as the web admin panel and the
 * public-facing calculator pages. No business logic lives here.
 */
class CalculatorController extends Controller
{
    public function __construct(
        protected CalculatorRepositoryInterface $repository,
        protected CalculatorServiceInterface $calculators,
    ) {
    }

    public function index(Request $request): CalculatorCollection
    {
        $filters = array_filter([
            'category_id' => $request->integer('category_id') ?: null,
            'search' => $request->string('search')->toString() ?: null,
            'is_featured' => $request->boolean('featured') ? true : null,
        ], static fn ($value) => $value !== null);

        $calculators = $this->repository->paginate(
            perPage: (int) $request->integer('per_page', 15),
            filters: $filters + ['is_active' => true],
        );

        return new CalculatorCollection($calculators);
    }

    public function show(string $slug): CalculatorResource
    {
        $calculator = $this->calculators->getBySlug($slug);

        return new CalculatorResource($calculator);
    }

    public function calculate(Request $request, string $slug): JsonResponse
    {
        $inputs = $request->input('inputs');

        if (! is_array($inputs)) {
            $inputs = $request->except(['inputs', '_token']);
        }

        if ($inputs === []) {
            return response()->json([
                'message' => 'The inputs field is required.',
                'errors' => ['inputs' => ['Provide an inputs object or flat field payload.']],
            ], 422);
        }

        try {
            $result = $this->calculators->calculate(
                slug: $slug,
                inputs: $inputs,
                userId: $request->user()?->id,
                meta: [
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->header('User-Agent'),
                ],
            );
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['data' => $result]);
    }
}
