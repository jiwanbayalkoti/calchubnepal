<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\ContactRequest;
use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Models\ContactMessage;
use App\Models\SeoPage;
use App\Models\SubscriptionPlan;
use App\Notifications\Admin\ContactMessageReceived;
use App\Services\Activity\ActivityLogService;
use App\Services\Admin\AdminNotifier;
use App\Services\Seo\PublicSitemapService;
use App\Services\Seo\SeoService;
use App\Support\CatalogStatsCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected SeoService $seo,
        protected ActivityLogService $activity,
        protected AdminNotifier $notifier,
        protected PublicSitemapService $publicSitemap,
    ) {
    }

    public function about(): View
    {
        $stats = Cache::remember(CatalogStatsCache::ABOUT_KEY, 900, function () {
            return [
                'calculators' => Calculator::query()->active()->count(),
                'categories' => CalculatorCategory::query()->active()->count(),
                'guides' => BlogPost::query()->published()->count(),
            ];
        });

        $coverageCategories = CalculatorCategory::query()
            ->active()
            ->ordered()
            ->whereHas('calculators', fn ($q) => $q->active())
            ->withCount(['calculators' => fn ($q) => $q->active()])
            ->take(10)
            ->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => __('about.meta_title'),
            'description' => __('about.meta_description'),
            'canonical' => route('about'),
        ]);

        return view('pages.about', [
            'meta' => $meta,
            'stats' => $stats,
            'coverageCategories' => $coverageCategories,
        ]);
    }

    public function pricing(): View
    {
        $plans = SubscriptionPlan::query()->active()->orderBy('sort_order')->orderBy('price')->get();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Pricing — AI Calculator Hub',
            'description' => 'Simple, transparent pricing for AI Calculator Hub. Start free, upgrade for PDF exports, unlimited saves and priority AI explanations.',
            'canonical' => route('pricing'),
        ]);

        return view('pages.pricing', ['plans' => $plans, 'meta' => $meta]);
    }

    public function contact(): View
    {
        $meta = $this->seo->buildMeta(null, [
            'title' => 'Contact Us — AI Calculator Hub',
            'description' => 'Get in touch with the AI Calculator Hub team for support, feedback or partnership enquiries.',
            'canonical' => route('contact'),
        ]);

        return view('pages.contact', ['meta' => $meta]);
    }

    public function contactStore(ContactRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $data['ip_address'] = $request->ip();
        $data['status'] = ContactMessage::STATUS_NEW;

        $message = ContactMessage::create($data);

        $this->activity->log('created', 'contact_message', $message, ['subject' => $message->subject]);
        $this->notifier->notify(new ContactMessageReceived($message));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => "Thanks for reaching out! We'll get back to you shortly.",
            ]);
        }

        return redirect()->route('contact')->with('status', "Thanks for reaching out! We'll get back to you shortly.");
    }

    public function privacy(): View
    {
        return $this->legalPage('privacy-policy', route('privacy'));
    }

    public function terms(): View
    {
        return $this->legalPage('terms-conditions', route('terms'));
    }

    public function cookies(): View
    {
        return $this->legalPage('cookie-policy', route('cookies'));
    }

    public function disclaimer(): View
    {
        return $this->legalPage('disclaimer', route('disclaimer'));
    }

    public function sitemap(): View
    {
        $categories = CalculatorCategory::query()
            ->active()
            ->ordered()
            ->withCount(['calculators' => fn ($q) => $q->active()])
            ->get();

        $calculators = Calculator::query()
            ->active()
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'icon']);

        $posts = BlogPost::query()
            ->published()
            ->latest('published_at')
            ->limit(12)
            ->get(['id', 'title', 'slug']);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Sitemap — CalchubNepal',
            'description' => 'HTML sitemap of CalchubNepal — calculators, free tools (QR & visiting card), categories, blog and legal pages.',
            'canonical' => route('sitemap'),
        ]);

        return view('pages.sitemap', [
            'meta' => $meta,
            'categories' => $categories,
            'calculators' => $calculators,
            'posts' => $posts,
            'mainLinks' => $this->publicSitemap->linksForGroup('main'),
            'toolLinks' => $this->publicSitemap->linksForGroup('tools'),
            'legalLinks' => array_merge(
                $this->publicSitemap->linksForGroup('legal'),
                $this->publicSitemap->accountLinks(),
            ),
        ]);
    }

    protected function legalPage(string $slug, string $canonical): View
    {
        $page = SeoPage::query()->active()->where('slug', $slug)->firstOrFail();

        $meta = $this->seo->buildMeta($page, [
            'title' => $page->meta_title ?: $page->title.' — AI Calculator Hub',
            'description' => $page->meta_description,
            'keywords' => $page->meta_keywords,
            'canonical' => $page->canonical_url ?: $canonical,
            'robots' => $page->robots ?: 'index,follow',
            'og_image' => $page->og_image,
        ]);

        return view('pages.legal', [
            'page' => $page,
            'meta' => $meta,
        ]);
    }
}
