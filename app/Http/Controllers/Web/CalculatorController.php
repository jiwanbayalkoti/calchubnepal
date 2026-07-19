<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Contracts\Services\CalculatorServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\CalculateRequest;
use App\Http\Requests\Web\ExplainRequest;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Services\Activity\ActivityLogService;
use App\Services\Ai\AiServiceInterface;
use App\Services\Ai\LocalExplanationBuilder;
use App\Services\Seo\SeoService;
use App\Services\Settings\AppSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CalculatorController extends Controller
{
    public function __construct(
        protected CalculatorServiceInterface $calculators,
        protected CalculatorRepositoryInterface $calculatorRepository,
        protected AiServiceInterface $ai,
        protected SeoService $seo,
        protected ActivityLogService $activity,
        protected AppSettings $hub,
    ) {
    }

    public function index(Request $request): View
    {
        $categories = CalculatorCategory::query()->active()->ordered()->get();

        $filters = array_filter([
            'category_id' => $request->query('category')
                ? $categories->firstWhere('slug', $request->query('category'))?->id
                : null,
            'search' => $request->query('q'),
        ]);

        $calculators = $this->calculatorRepository->paginate(12, $filters);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'All Calculators — AI Calculator Hub',
            'description' => 'Browse every free calculator on AI Calculator Hub: finance, health, construction, math, unit conversion and more.',
            'canonical' => route('calculators.index'),
        ]);

        return view('calculators.index', [
            'calculators' => $calculators,
            'categories' => $categories,
            'activeCategory' => $request->query('category'),
            'searchTerm' => $request->query('q'),
            'meta' => $meta,
        ]);
    }

    public function show(Calculator $calculator): View
    {
        abort_unless($calculator->is_active, 404);

        $calculator->increment('views_count');
        $calculator->load(['category', 'faqs' => fn ($q) => $q->active(), 'examples']);

        $related = $this->calculators->getRelated($calculator, 6);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Calculators', 'url' => route('calculators.index')],
            ['name' => $calculator->category->name, 'url' => route('categories.show', $calculator->category)],
            ['name' => $calculator->title, 'url' => url()->current()],
        ];

        $meta = $this->seo->buildMeta(null, [
            'title' => $calculator->meta_title ?: $calculator->title.' — Free Online Calculator',
            'description' => $calculator->meta_description ?: $calculator->short_description,
            'keywords' => $calculator->meta_keywords,
            'og_image' => $calculator->og_image,
            'canonical' => $calculator->canonical_url ?: route('calculators.show', $calculator),
        ]);

        $schemas = [
            $this->seo->calculatorSchema(
                $calculator->title,
                $calculator->short_description,
                route('calculators.show', $calculator),
            ),
            $this->seo->breadcrumbSchema($breadcrumbs),
        ];

        if ($calculator->faqs->isNotEmpty()) {
            $schemas[] = $this->seo->faqSchema(
                $calculator->faqs->map(fn ($faq) => [
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ])->all()
            );
        }

        $isFavorited = false;
        if ($user = request()->user()) {
            $isFavorited = $user->favorites()
                ->where('calculator_id', $calculator->id)
                ->exists();
        }

        return view('calculators.show', [
            'calculator' => $calculator,
            'related' => $related,
            'breadcrumbs' => $breadcrumbs,
            'meta' => $meta,
            'schemas' => $schemas,
            'isFavorited' => $isFavorited,
        ]);
    }

    public function calculate(CalculateRequest $request, string $calculator): JsonResponse
    {
        try {
            $result = $this->calculators->calculate(
                $calculator,
                $request->validated(),
                $request->user()?->id,
                [
                    'ip_address' => $request->ip(),
                    'user_agent' => (string) $request->userAgent(),
                ]
            );

            $this->activity->log('calculate', 'calculator', null, ['slug' => $calculator]);

            return response()->json([
                'success' => true,
                'data' => array_merge($result, [
                    'inputs' => $request->validated(),
                ]),
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e instanceof RuntimeException
                    ? $e->getMessage()
                    : 'Something went wrong while calculating. Please check your inputs and try again.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function explain(ExplainRequest $request, string $calculator): JsonResponse
    {
        if (! $this->hub->aiEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'AI explanations are currently disabled.',
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $calculatorModel = $this->calculators->getBySlug($calculator);

        $inputs = $request->input('inputs', []);
        $results = $request->input('results', []);
        $breakdown = $request->input('breakdown', []);
        $units = $request->input('units', []);

        try {
            $prompt = sprintf(
                "Explain, in simple friendly language for a general audience, how the following calculation was reached.\n\nCalculator: %s\nInputs: %s\nResults: %s\nBreakdown: %s\n\nKeep the explanation under 150 words, use short paragraphs, and avoid repeating raw JSON.",
                $calculatorModel->title,
                json_encode($inputs, JSON_PRETTY_PRINT),
                json_encode($results, JSON_PRETTY_PRINT),
                json_encode($breakdown, JSON_PRETTY_PRINT),
            );

            $response = $this->ai->generate($prompt, [
                'subject' => $calculatorModel,
            ], $request->user());

            $explanation = trim((string) ($response['content'] ?? ''));

            if ($explanation === '') {
                throw new RuntimeException('Empty AI response.');
            }

            return response()->json([
                'success' => true,
                'explanation' => $explanation,
                'source' => 'ai',
            ]);
        } catch (Throwable $e) {
            report($e);

            $fallback = app(LocalExplanationBuilder::class)->build(
                $calculatorModel->title,
                is_array($inputs) ? $inputs : [],
                is_array($results) ? $results : [],
                is_array($breakdown) ? $breakdown : [],
                is_array($units) ? $units : [],
                $calculatorModel->formula_description,
            );

            return response()->json([
                'success' => true,
                'explanation' => $fallback,
                'source' => 'local',
            ]);
        }
    }

    public function pdf(Request $request, string $calculator)
    {
        $calculatorModel = $this->calculators->getBySlug($calculator);

        $inputs = $this->decodeJsonField($request->input('inputs'));
        $results = $this->decodeJsonField($request->input('results'));
        $breakdown = $this->decodeJsonField($request->input('breakdown'));
        $units = $this->decodeJsonField($request->input('units'));

        $pdf = Pdf::loadView('calculators.pdf', [
            'calculator' => $calculatorModel,
            'inputs' => $inputs,
            'results' => $results,
            'breakdown' => $breakdown,
            'units' => $units,
            'generatedAt' => now(),
        ]);

        $this->activity->log('pdf_export', 'calculator', $calculatorModel);

        return $pdf->download($calculatorModel->slug.'-result.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonField(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
