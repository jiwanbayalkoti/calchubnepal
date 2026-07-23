<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    public function index(Request $request): View
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        return view('advertiser.advertisements.index', compact('advertiser'));
    }

    public function data(Request $request): JsonResponse
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $ads = Advertisement::query()
            ->forAdvertiser($advertiser->id)
            ->latest()
            ->get()
            ->map(function (Advertisement $ad) {
                $ad->syncRuntimeStatus();

                return [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'position' => $ad->position,
                    'banner_size' => $ad->banner_size ?: '—',
                    'link_url' => $ad->link_url,
                    'status' => $ad->status,
                    'is_running' => $ad->isCurrentlyRunning(),
                    'start_at' => $ad->start_at?->format('Y-m-d'),
                    'end_at' => $ad->end_at?->format('Y-m-d'),
                    'image_url' => $ad->image_url,
                    'impressions' => $ad->impressions,
                    'clicks' => $ad->clicks,
                    'ctr' => $ad->ctr(),
                ];
            });

        return response()->json(['data' => $ads]);
    }

    public function show(Request $request, int $id): View|JsonResponse
    {
        $advertiser = $request->user()->advertiser;
        abort_unless($advertiser, 403);

        $ad = Advertisement::query()
            ->with(['advertiser', 'assigner:id,name', 'creator:id,name'])
            ->forAdvertiser($advertiser->id)
            ->findOrFail($id);

        $this->authorize('view', $ad);
        $ad->syncRuntimeStatus();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'data' => [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'company_name' => $advertiser->company_name,
                    'position' => $ad->position,
                    'banner_size' => $ad->banner_size,
                    'link_url' => $ad->link_url,
                    'status' => $ad->status,
                    'start_at' => $ad->start_at?->format('Y-m-d H:i'),
                    'end_at' => $ad->end_at?->format('Y-m-d H:i'),
                    'assigned_by' => $ad->assigner?->name ?? $ad->creator?->name,
                    'created_at' => $ad->created_at?->format('Y-m-d H:i'),
                    'image_url' => $ad->image_url,
                    'impressions' => $ad->impressions,
                    'clicks' => $ad->clicks,
                    'ctr' => $ad->ctr(),
                ],
            ]);
        }

        return view('advertiser.advertisements.show', [
            'advertiser' => $advertiser,
            'ad' => $ad,
        ]);
    }
}
