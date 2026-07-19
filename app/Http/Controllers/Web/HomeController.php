<?php

namespace App\Http\Controllers\Web;

use App\Contracts\Services\CalculatorServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CalculatorCategory;
use App\Services\Seo\SeoService;
use App\Services\Settings\AppSettings;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected CalculatorServiceInterface $calculators,
        protected SeoService $seo,
        protected AppSettings $hub,
    ) {
    }

    public function index(): View
    {
        $popular = $this->calculators->getFeatured(8);

        if ($popular->count() < 8) {
            $popular = $popular->concat($this->calculators->getPopular(8))->unique('id')->take(8);
        }

        $categories = CalculatorCategory::query()
            ->active()
            ->ordered()
            ->withCount(['calculators' => fn ($q) => $q->where('is_active', true)])
            ->take(8)
            ->get();

        $latestPosts = BlogPost::query()
            ->published()
            ->with('category')
            ->latest('published_at')
            ->take(3)
            ->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => $this->hub->homeTitle(),
            'description' => $this->hub->homeDescription(),
            'canonical' => url('/'),
        ]);

        return view('home', [
            'meta' => $meta,
            'popularCalculators' => $popular,
            'categories' => $categories,
            'latestPosts' => $latestPosts,
        ]);
    }
}
