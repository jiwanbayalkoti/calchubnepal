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
use App\Services\Activity\ActivityLogService;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function __construct(
        protected SeoService $seo,
        protected ActivityLogService $activity,
    ) {
    }

    public function about(): View
    {
        $meta = $this->seo->buildMeta(null, [
            'title' => 'About Us — AI Calculator Hub',
            'description' => 'Learn about AI Calculator Hub — our mission to make accurate, AI-assisted calculators free and accessible for everyone.',
            'canonical' => route('about'),
        ]);

        return view('pages.about', ['meta' => $meta]);
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

        if ($request->wantsJson()) {
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
            'title' => 'Sitemap — AI Calculator Hub',
            'description' => 'HTML sitemap of AI Calculator Hub pages, categories, calculators and blog posts.',
            'canonical' => route('sitemap'),
        ]);

        return view('pages.sitemap', [
            'meta' => $meta,
            'categories' => $categories,
            'calculators' => $calculators,
            'posts' => $posts,
            'mainLinks' => [
                ['label' => 'Home', 'url' => route('home')],
                ['label' => 'All Calculators', 'url' => route('calculators.index')],
                ['label' => 'Categories', 'url' => route('categories.index')],
                ['label' => 'Blog', 'url' => route('blog.index')],
                ['label' => 'Pricing', 'url' => route('pricing')],
                ['label' => 'About Us', 'url' => route('about')],
                ['label' => 'Contact', 'url' => route('contact')],
                ['label' => 'Search', 'url' => route('search.results')],
            ],
            'legalLinks' => [
                ['label' => 'Privacy Policy', 'url' => route('privacy')],
                ['label' => 'Terms & Conditions', 'url' => route('terms')],
                ['label' => 'Cookie Policy', 'url' => route('cookies')],
                ['label' => 'Disclaimer', 'url' => route('disclaimer')],
                ['label' => 'XML Sitemap', 'url' => route('sitemap.xml')],
                ['label' => 'Login', 'url' => route('login')],
                ['label' => 'Register', 'url' => route('register')],
            ],
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
