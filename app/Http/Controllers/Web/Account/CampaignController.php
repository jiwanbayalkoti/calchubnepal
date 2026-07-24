<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Models\QrCampaign;
use App\Services\Qr\EnterpriseAnalyticsService;
use App\Services\Seo\SeoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function __construct(
        protected EnterpriseAnalyticsService $analytics,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        return view('account.campaigns.index', [
            'user' => $request->user(),
            'campaigns' => $request->user()->qrCampaigns()->withCount('qrCodes')->latest()->paginate(12),
            'workspaces' => $request->user()->qrWorkspaces()->get(),
            'meta' => $this->seo->buildMeta(null, [
                'title' => 'Campaigns — CalchubNepal',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'utm_source' => ['nullable', 'string', 'max:80'],
            'utm_medium' => ['nullable', 'string', 'max:80'],
            'utm_campaign' => ['nullable', 'string', 'max:80'],
            'workspace_id' => ['nullable', 'integer'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        QrCampaign::query()->create([
            'user_id' => $request->user()->id,
            'workspace_id' => $data['workspace_id'] ?? null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'utm_source' => $data['utm_source'] ?? null,
            'utm_medium' => $data['utm_medium'] ?? null,
            'utm_campaign' => $data['utm_campaign'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => 'active',
        ]);

        return back()->with('status', 'campaign-created');
    }

    public function show(Request $request, QrCampaign $campaign): View
    {
        abort_unless((int) $campaign->user_id === (int) $request->user()->id, 403);

        return view('account.campaigns.show', [
            'user' => $request->user(),
            'campaign' => $campaign->load('qrCodes'),
            'report' => $this->analytics->campaignReport($campaign),
            'meta' => $this->seo->buildMeta(null, [
                'title' => $campaign->name.' — Campaign',
                'robots' => 'noindex,nofollow',
            ]),
        ]);
    }

    public function destroy(Request $request, QrCampaign $campaign): RedirectResponse
    {
        abort_unless((int) $campaign->user_id === (int) $request->user()->id, 403);
        $campaign->qrCodes()->update(['campaign_id' => null]);
        $campaign->delete();

        return redirect()->route('account.campaigns.index')->with('status', 'campaign-deleted');
    }
}
