<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiLog;
use App\Models\Calculator;
use App\Models\CalculationHistory;
use App\Models\User;
use App\Services\Activity\ActivityLogService;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    public function index(): View
    {
        $stats = [
            'users_count' => User::query()->count(),
            'calculators_count' => Calculator::query()->count(),
            'usage_today' => CalculationHistory::query()->whereDate('created_at', today())->count(),
            'ai_requests_count' => AiLog::query()->count(),
        ];

        $chart = $this->last7DaysUsage();

        $recentActivity = $this->activityLog->recent(10);

        return view('admin.dashboard', [
            'stats' => $stats,
            'chartLabels' => $chart['labels'],
            'chartData' => $chart['data'],
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * @return array{labels: array<int, string>, data: array<int, int>}
     */
    private function last7DaysUsage(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('M d');
            $data[] = CalculationHistory::query()->whereDate('created_at', $date)->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
