<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CalculatorCategory;
use App\Services\Seo\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(protected SeoService $seo)
    {
    }

    public function show(Request $request, CalculatorCategory $category): View
    {
        abort_unless($category->is_active, 404);

        $calculators = $category->calculators()
            ->active()
            ->ordered()
            ->paginate(12)
            ->withQueryString();

        $categories = CalculatorCategory::query()->active()->ordered()->get();

        $breadcrumbs = [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Categories', 'url' => route('categories.index')],
            ['name' => $category->name, 'url' => url()->current()],
        ];

        $meta = $this->seo->buildMeta(null, [
            'title' => $category->meta_title ?: $category->name.' Calculators — AI Calculator Hub',
            'description' => $category->meta_description ?: $category->description,
            'canonical' => route('categories.show', $category),
        ]);

        return view('categories.show', [
            'category' => $category,
            'calculators' => $calculators,
            'categories' => $categories,
            'breadcrumbs' => $breadcrumbs,
            'meta' => $meta,
            'breadcrumbSchema' => $this->seo->breadcrumbSchema($breadcrumbs),
        ]);
    }

    public function index(): View
    {
        $categories = CalculatorCategory::query()
            ->active()
            ->ordered()
            ->withCount(['calculators' => fn ($q) => $q->where('is_active', true)])
            ->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Calculator Categories — AI Calculator Hub',
            'description' => 'Browse every calculator category: finance, health, construction, math, unit conversion and more.',
            'canonical' => route('categories.index'),
        ]);

        return view('categories.index', [
            'categories' => $categories,
            'meta' => $meta,
        ]);
    }
}
