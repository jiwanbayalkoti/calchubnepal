<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Services\Qr\EnterpriseAnalyticsService;
use App\Services\Seo\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrEnterpriseDashboardController extends Controller
{
    public function __construct(
        protected EnterpriseAnalyticsService $analytics,
        protected SeoService $seo,
    ) {
    }

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $report = $this->analytics->userEnterpriseDashboard($user);

        return view('account.qr-enterprise.dashboard', [
            'user' => $user,
            'report' => $report,
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'QR Enterprise Dashboard — CalchubNepal',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }
}
