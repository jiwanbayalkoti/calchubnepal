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
    public function index(): View
    {
        $popularCalculators = Calculator::query()
            ->orderByDesc('usage_count')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'usage_count', 'views_count']);

        $pageViewsSummary = [
            'today' => PageView::query()->whereDate('created_at', today())->count(),
            'this_week' => PageView::query()->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'this_month' => PageView::query()->where('created_at', '>=', Carbon::now()->subDays(30))->count(),
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
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get();

        $deviceSplit = PageView::query()
            ->select('device', DB::raw('COUNT(*) as views'))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('device')
            ->orderByDesc('views')
            ->pluck('views', 'device');

        return view('admin.analytics.index', compact(
            'popularCalculators',
            'pageViewsSummary',
            'usageSummary',
            'topPaths',
            'deviceSplit',
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
