<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\CalculationHistory;
use App\Models\Calculator;
use App\Models\PageView;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $usageSummary = [
            'calculations_today' => CalculationHistory::query()->whereDate('created_at', today())->count(),
            'calculations_week' => CalculationHistory::query()->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'ai_today' => AiLog::query()->whereDate('created_at', today())->count(),
            'ai_total' => AiLog::query()->count(),
        ];

        $topPaths = PageView::query()
            ->select('path', DB::raw('COUNT(*) as views'))
            ->where('created_at', '>=', $since30)
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $deviceSplit = PageView::query()
            ->select('device', DB::raw('COUNT(*) as views'))
            ->where('created_at', '>=', $since30)
            ->groupBy('device')
            ->orderByDesc('views')
            ->pluck('views', 'device');

        $views30 = max(1, (int) PageView::query()->where('created_at', '>=', $since30)->count());

        $countryRows = PageView::query()
            ->select('country', DB::raw('COUNT(*) as views'))
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
                    'views' => (int) $row->views,
                    'share' => round(($row->views / $views30) * 100, 1),
                ];
            });

        $unknownCountryViews = PageView::query()
            ->where('created_at', '>=', $since30)
            ->where(function ($q) {
                $q->whereNull('country')->orWhere('country', '');
            })
            ->count();

        $recentVisits = PageView::query()
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->latest('created_at')
            ->limit(50)
            ->get(['id', 'path', 'country', 'device', 'ip_truncated', 'referrer', 'created_at']);

        return view('admin.analytics.index', compact(
            'popularCalculators',
            'pageViewsSummary',
            'usageSummary',
            'topPaths',
            'deviceSplit',
            'countryRows',
            'unknownCountryViews',
            'recentVisits',
        ));
    }

    public function pageViewsChart(): JsonResponse
    {
        $labels = [];
        $pageViews = [];
        $calculations = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $pageViews[] = PageView::query()->whereDate('created_at', $date)->count();
            $calculations[] = CalculationHistory::query()->whereDate('created_at', $date)->count();
        }

        return response()->json([
            'labels' => $labels,
            'page_views' => $pageViews,
            'calculations' => $calculations,
            // Back-compat for older JS expecting `data`
            'data' => $pageViews,
        ]);
    }
}
