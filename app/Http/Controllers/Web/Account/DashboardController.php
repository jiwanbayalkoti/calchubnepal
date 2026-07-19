<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Services\Seo\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $recentHistory = $user->histories()
            ->with('calculator:id,title,slug,icon')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $favorites = $user->favorites()
            ->with('calculator:id,title,slug,icon,short_description,is_premium')
            ->latest()
            ->limit(6)
            ->get();

        $savedCount = $user->savedCalculations()->count();
        $historyCount = $user->histories()->count();
        $favoritesCount = $user->favorites()->count();
        $savedLimit = $user->savedCalculationsLimit();

        $subscription = $user->activeSubscription()->with('plan')->first();

        $meta = $this->seo->buildMeta(null, [
            'title' => 'My Account — AI Calculator Hub',
            'description' => 'Your dashboard: recent calculations, favorites, saved results and subscription status.',
            'canonical' => route('account.dashboard'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.dashboard', [
            'user' => $user,
            'recentHistory' => $recentHistory,
            'favorites' => $favorites,
            'savedCount' => $savedCount,
            'historyCount' => $historyCount,
            'favoritesCount' => $favoritesCount,
            'savedLimit' => $savedLimit,
            'subscription' => $subscription,
            'meta' => $meta,
        ]);
    }
}
