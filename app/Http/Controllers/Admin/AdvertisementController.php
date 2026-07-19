<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\BuildsDataTableResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvertisementRequest;
use App\Models\Advertisement;
use App\Services\Activity\ActivityLogService;
use App\Services\Media\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdvertisementController extends Controller
{
    use BuildsDataTableResponse;

    public function __construct(
        protected ActivityLogService $activityLog,
        protected FileUploadService $uploads,
    ) {
    }

    public function index(): View
    {
        return view('admin.advertisements.index', [
            'adPositions' => config('calculator_hub.ads.positions', []),
            'maxUploadKb' => (int) config('calculator_hub.ads.max_upload_kb', 1024),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $query = Advertisement::query();

        return $this->toDataTableResponse(
            $request,
            $query,
            searchableColumns: ['name', 'position', 'ad_type'],
            orderableColumns: ['name', 'position', 'ad_type', 'is_active', 'impressions', 'clicks', 'created_at'],
            transform: function (Advertisement $ad) {
                return [
                    'id' => $ad->id,
                    'name' => $ad->name,
                    'position' => $ad->position,
                    'ad_type' => $ad->ad_type,
                    'is_active' => (bool) $ad->is_active,
                    'impressions' => $ad->impressions,
                    'clicks' => $ad->clicks,
                    'start_at' => $ad->start_at?->format('Y-m-d'),
                    'end_at' => $ad->end_at?->format('Y-m-d'),
                ];
            }
        );
    }

    public function store(AdvertisementRequest $request): JsonResponse
    {
        $data = $this->payloadFromRequest($request);
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(4);
        $data['is_active'] = $data['is_active'] ?? true;
        $data['created_by'] = $request->user()?->id;
        $data['image'] = $this->resolveImagePath($request);

        $ad = Advertisement::create($data);

        $this->forgetAdCaches($ad->position);
        $this->activityLog->log('create', 'advertisements', $ad, ['name' => $ad->name]);

        return response()->json(['message' => 'Advertisement created successfully.', 'data' => $this->toResponseData($ad)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);

        return response()->json(['data' => $this->toResponseData($ad)]);
    }

    public function update(AdvertisementRequest $request, int $id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $oldPosition = $ad->position;
        $oldImage = $ad->image;

        $data = $this->payloadFromRequest($request);
        $data['updated_by'] = $request->user()?->id;

        $newImage = $this->resolveImagePath($request, $ad);
        if ($newImage !== false) {
            $data['image'] = $newImage;
            if ($ad->hasLocalImage() && $oldImage && $oldImage !== $newImage) {
                $this->uploads->deletePublic($oldImage);
            }
        }

        $ad->update($data);

        $this->forgetAdCaches($oldPosition);
        if ($ad->position !== $oldPosition) {
            $this->forgetAdCaches($ad->position);
        }

        $this->activityLog->log('update', 'advertisements', $ad, ['name' => $ad->name]);

        return response()->json(['message' => 'Advertisement updated successfully.', 'data' => $this->toResponseData($ad->fresh())]);
    }

    public function destroy(int $id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $name = $ad->name;
        $position = $ad->position;

        if ($ad->hasLocalImage()) {
            $this->uploads->deletePublic($ad->image);
        }

        $ad->delete();

        $this->forgetAdCaches($position);
        $this->activityLog->log('delete', 'advertisements', null, ['name' => $name]);

        return response()->json(['message' => 'Advertisement deleted successfully.']);
    }

    public function toggleActive(int $id): JsonResponse
    {
        $ad = Advertisement::findOrFail($id);
        $ad->update(['is_active' => ! $ad->is_active]);
        $this->forgetAdCaches($ad->position);

        return response()->json(['message' => 'Status updated successfully.', 'data' => $this->toResponseData($ad)]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payloadFromRequest(AdvertisementRequest $request): array
    {
        return $request->safe()->except(['image_file', 'remove_image', 'image']);
    }

    /**
     * Resolve the stored image value.
     * Returns false when the existing image should be left unchanged (update only).
     */
    protected function resolveImagePath(AdvertisementRequest $request, ?Advertisement $existing = null): string|false|null
    {
        if ($request->hasFile('image_file')) {
            return $this->uploads->storePublic($request->file('image_file'), 'advertisements');
        }

        if ($request->boolean('remove_image')) {
            return null;
        }

        if ($request->filled('image')) {
            return $request->input('image');
        }

        // Create: no image. Update: keep current.
        return $existing ? false : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function toResponseData(Advertisement $ad): array
    {
        return array_merge($ad->toArray(), [
            'image_url' => $ad->image_url,
            'start_at' => $ad->start_at?->format('Y-m-d'),
            'end_at' => $ad->end_at?->format('Y-m-d'),
        ]);
    }

    protected function forgetAdCaches(?string $position = null): void
    {
        $positions = $position
            ? [$position]
            : array_keys(config('calculator_hub.ads.positions', [
                'header' => true,
                'sidebar' => true,
                'sticky' => true,
                'footer' => true,
                'in_content' => true,
                'between_results' => true,
            ]));

        foreach ($positions as $slot) {
            Cache::forget("calc_hub:ads:{$slot}");
        }
    }
}
