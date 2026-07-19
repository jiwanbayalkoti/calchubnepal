<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Services\CalculatorServiceInterface;
use App\Http\Controllers\Controller;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected CalculatorServiceInterface $calculators,
        protected SeoService $seo,
    ) {
    }

    public function results(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));

        $results = $this->calculators->search($term, 12);

        $meta = $this->seo->buildMeta(null, [
            'title' => $term !== '' ? "Search results for \"{$term}\" — AI Calculator Hub" : 'Search — AI Calculator Hub',
            'description' => 'Search hundreds of free calculators across finance, health, construction, math and more.',
            'canonical' => route('search.results', ['q' => $term]),
            'robots' => 'noindex,follow',
        ]);

        return view('search.results', [
            'term' => $term,
            'results' => $results,
            'meta' => $meta,
        ]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $term = trim((string) $request->query('q', ''));

        if (mb_strlen($term) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $calculators = $this->calculators->search($term, 8);

        $suggestions = collect($calculators->items())->map(fn ($calculator) => [
            'title' => $calculator->title,
            'url' => route('calculators.show', $calculator),
            'category' => $calculator->category?->name,
            'icon' => $calculator->icon,
        ])->values();

        return response()->json(['suggestions' => $suggestions]);
    }
}
