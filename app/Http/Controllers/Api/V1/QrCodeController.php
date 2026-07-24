<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\DynamicQrStoreRequest;
use App\Http\Requests\Web\DynamicQrUpdateRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Services\Qr\DynamicQrService;
use App\Services\Qr\QrScanAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;
use Throwable;

class QrCodeController extends Controller
{
    public function __construct(
        protected DynamicQrService $dynamic,
        protected QrScanAnalyticsService $analytics,
    ) {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $codes = $request->user()
            ->dynamicQrCodes()
            ->paginate((int) $request->integer('per_page', 15));

        return QrCodeResource::collection($codes);
    }

    public function store(DynamicQrStoreRequest $request): JsonResponse
    {
        try {
            $created = $this->dynamic->create(
                $request->payload(),
                $request->file('logo'),
                $request->user()->id
            );

            return response()->json([
                'data' => (new QrCodeResource($created['qr']))->resolve(),
                'image' => $created['image'],
                'short_url' => $created['short_url'],
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'Unable to create dynamic QR code.'], 500);
        }
    }

    public function show(Request $request, QrCode $qrCode): QrCodeResource
    {
        $this->authorize('view', $qrCode);
        abort_unless($qrCode->is_dynamic, 404);

        return new QrCodeResource($qrCode);
    }

    public function update(DynamicQrUpdateRequest $request, QrCode $qrCode): QrCodeResource|JsonResponse
    {
        try {
            $updated = $this->dynamic->update($qrCode, $request->validated());

            return new QrCodeResource($updated);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function destroy(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('delete', $qrCode);
        $this->dynamic->delete($qrCode);

        return response()->json(['message' => 'Deleted']);
    }

    public function analytics(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('view', $qrCode);
        abort_unless($qrCode->is_dynamic, 404);

        return response()->json([
            'data' => $this->analytics->report(
                $qrCode,
                (int) $request->integer('daily_days', 30),
                (int) $request->integer('monthly_months', 12),
            ),
        ]);
    }

    public function scans(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorize('view', $qrCode);
        abort_unless($qrCode->is_dynamic, 404);

        $scans = $qrCode->scans()
            ->latest('scanned_at')
            ->paginate((int) $request->integer('per_page', 50));

        return response()->json([
            'data' => $scans->getCollection()->map(fn ($scan) => [
                'scanned_at' => $scan->scanned_at?->toIso8601String(),
                'country' => $scan->country,
                'device' => $scan->device,
                'browser' => $scan->browser,
                'os' => $scan->os,
                'referrer' => $scan->referrer,
            ]),
            'meta' => [
                'current_page' => $scans->currentPage(),
                'last_page' => $scans->lastPage(),
                'per_page' => $scans->perPage(),
                'total' => $scans->total(),
            ],
        ]);
    }
}
