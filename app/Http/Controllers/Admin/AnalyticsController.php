<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\BlogPost;
use App\Models\CalculationHistory;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /** @var array<string, string> */
    private const COUNTRY_NAMES = [
        'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AR' => 'Argentina', 'AU' => 'Australia',
        'AT' => 'Austria', 'BD' => 'Bangladesh', 'BE' => 'Belgium', 'BR' => 'Brazil', 'BG' => 'Bulgaria',
        'KH' => 'Cambodia', 'CA' => 'Canada', 'CL' => 'Chile', 'CN' => 'China', 'CO' => 'Colombia',
        'HR' => 'Croatia', 'CZ' => 'Czechia', 'DK' => 'Denmark', 'EG' => 'Egypt', 'EE' => 'Estonia',
        'FI' => 'Finland', 'FR' => 'France', 'DE' => 'Germany', 'GH' => 'Ghana', 'GR' => 'Greece',
        'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IN' => 'India', 'ID' => 'Indonesia', 'IE' => 'Ireland',
        'IL' => 'Israel', 'IT' => 'Italy', 'JP' => 'Japan', 'JO' => 'Jordan', 'KE' => 'Kenya',
        'KR' => 'South Korea', 'KW' => 'Kuwait', 'LV' => 'Latvia', 'LT' => 'Lithuania', 'MY' => 'Malaysia',
        'MV' => 'Maldives', 'MX' => 'Mexico', 'MA' => 'Morocco', 'MM' => 'Myanmar', 'NP' => 'Nepal',
        'NL' => 'Netherlands', 'NZ' => 'New Zealand', 'NG' => 'Nigeria', 'NO' => 'Norway', 'PK' => 'Pakistan',
        'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 'QA' => 'Qatar',
        'RO' => 'Romania', 'RU' => 'Russia', 'SA' => 'Saudi Arabia', 'RS' => 'Serbia', 'SG' => 'Singapore',
        'SK' => 'Slovakia', 'ZA' => 'South Africa', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SE' => 'Sweden',
        'CH' => 'Switzerland', 'TW' => 'Taiwan', 'TH' => 'Thailand', 'TR' => 'Turkey', 'UA' => 'Ukraine',
        'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'VN' => 'Vietnam',
    ];

    public function index(): View
    {
        $since30 = Carbon::now()->subDays(30);

        $popularCalculators = Calculator::query()
            ->orderByDesc('usage_count')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'usage_count', 'views_count']);

        $pageViewsSummary = [
            'today' => PageView::query()->whereDate('created_at', today())->count(),
            'this_week' => PageView::query()->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'this_month' => PageView::query()->where('created_at', '>=', $since30)->count(),
            'total' => PageView::query()->count(),
        ];

        // Unique visitors: distinct session (fallback hashed IP) — not the same as page hits.
        $siteViewsSummary = [
            'today' => $this->countSiteViews(fn ($q) => $q->whereDate('created_at', today())),
            'this_week' => $this->countSiteViews(fn ($q) => $q->where('created_at', '>=', Carbon::now()->subDays(7))),
            'this_month' => $this->countSiteViews(fn ($q) => $q->where('created_at', '>=', $since30)),
            'total' => $this->countSiteViews(),
        ];

        $usageSummary = [
            'calculations_today' => CalculationHistory::query()->whereDate('created_at', today())->count(),
            'calculations_week' => CalculationHistory::query()->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'ai_today' => AiLog::query()->whereDate('created_at', today())->count(),
            'ai_total' => AiLog::query()->count(),
        ];

        $popularPages = $this->buildPopularPages(
            PageView::query()
                ->select('path', DB::raw('COUNT(*) as views'))
                ->where('created_at', '>=', $since30)
                ->groupBy('path')
                ->orderByDesc('views')
                ->limit(15)
                ->get()
        );

        $deviceSplit = PageView::query()
            ->select('device', DB::raw('COUNT(*) as views'))
            ->where('created_at', '>=', $since30)
            ->groupBy('device')
            ->orderByDesc('views')
            ->pluck('views', 'device');

        $views30 = max(1, (int) PageView::query()->where('created_at', '>=', $since30)->count());

        $countryRows = PageView::query()
            ->select(
                'country',
                DB::raw('COUNT(*) as views'),
                DB::raw("COUNT(DISTINCT COALESCE(NULLIF(session_id, ''), ip_hash)) as visitors")
            )
            ->where('created_at', '>=', $since30)
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->orderByDesc('views')
            ->limit(25)
            ->get()
            ->map(function ($row) use ($views30) {
                $code = strtoupper((string) $row->country);

                return (object) [
                    'code' => $code,
                    'name' => self::COUNTRY_NAMES[$code] ?? $code,
                    'flag' => $this->countryFlagEmoji($code),
                    'views' => (int) $row->views,
                    'visitors' => (int) $row->visitors,
                    'share' => round(($row->views / $views30) * 100, 1),
                ];
            });

        $unknownCountryViews = PageView::query()
            ->where('created_at', '>=', $since30)
            ->where(function ($q) {
                $q->whereNull('country')->orWhere('country', '');
            })
            ->count();

        $unknownCountryVisitors = $this->countSiteViews(function ($q) use ($since30) {
            $q->where('created_at', '>=', $since30)
                ->where(function ($inner) {
                    $inner->whereNull('country')->orWhere('country', '');
                });
        });

        if ($unknownCountryViews > 0) {
            $countryRows = $countryRows->push((object) [
                'code' => '—',
                'name' => 'Unknown / local',
                'flag' => '🌐',
                'views' => $unknownCountryViews,
                'visitors' => $unknownCountryVisitors,
                'share' => round(($unknownCountryViews / $views30) * 100, 1),
            ]);
        }

        $countryKnownShare = round((($views30 - $unknownCountryViews) / $views30) * 100, 1);

        $recentVisits = PageView::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latest('created_at')
            ->limit(50)
            ->get(['id', 'path', 'country', 'device', 'ip_truncated', 'referrer', 'created_at']);

        return view('admin.analytics.index', compact(
            'popularCalculators',
            'pageViewsSummary',
            'siteViewsSummary',
            'usageSummary',
            'popularPages',
            'deviceSplit',
            'countryRows',
            'unknownCountryViews',
            'countryKnownShare',
            'views30',
            'recentVisits',
        ));
    }

    public function pageViewsChart(): JsonResponse
    {
        $labels = [];
        $pageViews = [];
        $siteViews = [];
        $calculations = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $pageViews[] = PageView::query()->whereDate('created_at', $date)->count();
            $siteViews[] = $this->countSiteViews(fn ($q) => $q->whereDate('created_at', $date));
            $calculations[] = CalculationHistory::query()->whereDate('created_at', $date)->count();
        }

        return response()->json([
            'labels' => $labels,
            'page_views' => $pageViews,
            'site_views' => $siteViews,
            'calculations' => $calculations,
            // Back-compat for older JS expecting `data`
            'data' => $pageViews,
        ]);
    }

    /**
     * Count unique site visits (session_id, else ip_hash) within an optional scope.
     *
     * @param  (callable(\Illuminate\Database\Eloquent\Builder): mixed)|null  $scope
     */
    protected function countSiteViews(?callable $scope = null): int
    {
        $query = PageView::query();

        if ($scope) {
            $scope($query);
        }

        return (int) $query
            ->selectRaw("COUNT(DISTINCT COALESCE(NULLIF(session_id, ''), ip_hash)) as aggregate")
            ->value('aggregate');
    }

    protected function countryFlagEmoji(string $code): string
    {
        if (! preg_match('/^[A-Z]{2}$/', $code)) {
            return '🌐';
        }

        $chars = array_map(
            static fn (string $c) => mb_chr(0x1F1E6 + (ord($c) - ord('A'))),
            str_split($code)
        );

        return implode('', $chars);
    }

    /**
     * Turn raw path rows into friendly popular-page rows.
     *
     * @param  Collection<int, object{path: string, views: int|string}>  $rows
     * @return Collection<int, object>
     */
    protected function buildPopularPages(Collection $rows): Collection
    {
        $static = [
            '/' => ['title' => 'Home', 'type' => 'Page'],
            '/calculators' => ['title' => 'All Calculators', 'type' => 'Page'],
            '/categories' => ['title' => 'Categories', 'type' => 'Page'],
            '/blog' => ['title' => 'Blog', 'type' => 'Page'],
            '/pricing' => ['title' => 'Pricing', 'type' => 'Page'],
            '/about' => ['title' => 'About', 'type' => 'Page'],
            '/contact' => ['title' => 'Contact', 'type' => 'Page'],
            '/search' => ['title' => 'Search', 'type' => 'Page'],
            '/privacy-policy' => ['title' => 'Privacy Policy', 'type' => 'Legal'],
            '/terms-conditions' => ['title' => 'Terms & Conditions', 'type' => 'Legal'],
            '/cookie-policy' => ['title' => 'Cookie Policy', 'type' => 'Legal'],
            '/disclaimer' => ['title' => 'Disclaimer', 'type' => 'Legal'],
            '/sitemap' => ['title' => 'Sitemap', 'type' => 'Page'],
            '/account' => ['title' => 'Account Dashboard', 'type' => 'Account'],
            '/account/profile' => ['title' => 'Account Profile', 'type' => 'Account'],
            '/account/history' => ['title' => 'Calculation History', 'type' => 'Account'],
            '/account/favorites' => ['title' => 'Favorites', 'type' => 'Account'],
            '/account/saved' => ['title' => 'Saved Calculations', 'type' => 'Account'],
            '/account/subscription' => ['title' => 'Subscription', 'type' => 'Account'],
        ];

        $calculatorSlugs = [];
        $categorySlugs = [];
        $blogSlugs = [];

        foreach ($rows as $row) {
            $path = '/'.ltrim((string) $row->path, '/');
            if ($path === '//') {
                $path = '/';
            }

            if (preg_match('#^/calculator/([^/]+)$#', $path, $m)) {
                $calculatorSlugs[] = $m[1];
            } elseif (preg_match('#^/category/([^/]+)$#', $path, $m)) {
                $categorySlugs[] = $m[1];
            } elseif (preg_match('#^/blog/([^/]+)$#', $path, $m)) {
                $blogSlugs[] = $m[1];
            }
        }

        $calculators = $calculatorSlugs
            ? Calculator::query()->whereIn('slug', array_unique($calculatorSlugs))->get(['slug', 'title'])->keyBy('slug')
            : collect();
        $categories = $categorySlugs
            ? CalculatorCategory::query()->whereIn('slug', array_unique($categorySlugs))->get(['slug', 'name'])->keyBy('slug')
            : collect();
        $posts = $blogSlugs
            ? BlogPost::query()->whereIn('slug', array_unique($blogSlugs))->get(['slug', 'title'])->keyBy('slug')
            : collect();

        return $rows->map(function ($row) use ($static, $calculators, $categories, $posts) {
            $path = '/'.ltrim((string) $row->path, '/');
            if ($path === '//') {
                $path = '/';
            }

            $title = null;
            $type = 'Page';

            if (isset($static[$path])) {
                $title = $static[$path]['title'];
                $type = $static[$path]['type'];
            } elseif (preg_match('#^/calculator/([^/]+)$#', $path, $m)) {
                $title = $calculators->get($m[1])?->title ?? Str::headline(str_replace('-', ' ', $m[1]));
                $type = 'Calculator';
            } elseif (preg_match('#^/category/([^/]+)$#', $path, $m)) {
                $title = $categories->get($m[1])?->name ?? Str::headline(str_replace('-', ' ', $m[1]));
                $type = 'Category';
            } elseif (preg_match('#^/blog/([^/]+)$#', $path, $m)) {
                $title = $posts->get($m[1])?->title ?? Str::headline(str_replace('-', ' ', $m[1]));
                $type = 'Blog';
            } elseif (str_starts_with($path, '/account')) {
                $title = 'Account';
                $type = 'Account';
            } else {
                $title = Str::headline(trim(str_replace(['/', '-'], [' ', ' '], $path))) ?: 'Page';
            }

            return (object) [
                'title' => $title,
                'type' => $type,
                'path' => $path,
                'url' => url($path === '/' ? '/' : ltrim($path, '/')),
                'views' => (int) $row->views,
            ];
        });
    }
}
