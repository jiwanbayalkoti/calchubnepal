<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use App\Services\Ads\AdTrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdTrackingController extends Controller
{
    public function __construct(protected AdTrackingService $tracking)
    {
    }

    public function impression(Request $request, int $id): Response|JsonResponse
    {
        $ad = Advertisement::query()->find($id);
        if ($ad && $ad->isCurrentlyRunning()) {
            $this->tracking->trackImpression($ad, $request);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        // 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function click(Request $request, int $id): RedirectResponse
    {
        $ad = Advertisement::query()->findOrFail($id);
        $target = $this->tracking->trackClick($ad, $request) ?: url('/');

        return redirect()->away($target);
    }

    public function adsenseImpression(Request $request): Response|JsonResponse
    {
        $position = (string) $request->query('position', 'sidebar');
        $slot = $request->query('slot');

        $this->tracking->trackAdsenseUnit(
            $request,
            $position,
            is_string($slot) ? $slot : null,
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true]);
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }
}
