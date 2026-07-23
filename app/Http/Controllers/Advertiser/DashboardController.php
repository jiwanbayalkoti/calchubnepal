<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Models\AdvertisementClick;
use App\Models\AdvertisementImpression;
use App\Services\Ads\AdvertiserReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected AdvertiserReportService $reports)
    {
    }

    public function index(Request $request): View
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $ads = Advertisement::query()
            ->forAdvertiser($advertiser->id)
            ->get();

        $activeAds = $ads->filter(fn (Advertisement $ad) => $ad->isCurrentlyRunning())->count();
        $totalImpressions = (int) $ads->sum('impressions');
        $totalClicks = (int) $ads->sum('clicks');
        $ctr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0.0;

        $runningDays = $ads->max(fn (Advertisement $ad) => $ad->runningDays()) ?: 0;
        $remainingDays = $ads
            ->filter(fn (Advertisement $ad) => $ad->remainingDays() !== null)
            ->min(fn (Advertisement $ad) => $ad->remainingDays());

        $range = $this->reports->resolveRange('last_7');
        $series = $this->reports->dailySeries($advertiser, $range['from'], $range['to']);

        $recentClicks = AdvertisementClick::query()
            ->with('advertisement:id,name')
            ->where('advertiser_id', $advertiser->id)
            ->latest('clicked_at')
            ->limit(8)
            ->get();

        $recentImpressions = AdvertisementImpression::query()
            ->with('advertisement:id,name')
            ->where('advertiser_id', $advertiser->id)
            ->latest('viewed_at')
            ->limit(8)
            ->get();

        return view('advertiser.dashboard', compact(
            'advertiser',
            'activeAds',
            'totalImpressions',
            'totalClicks',
            'ctr',
            'runningDays',
            'remainingDays',
            'series',
            'recentClicks',
            'recentImpressions',
            'ads',
        ));
    }
}
