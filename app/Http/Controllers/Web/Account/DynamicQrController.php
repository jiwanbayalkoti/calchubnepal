<?php

namespace App\Http\Controllers\Web\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\DynamicQrUpdateRequest;
use App\Models\QrCode;
use App\Services\Qr\DynamicQrService;
use App\Services\Qr\QrScanAnalyticsService;
use App\Services\Seo\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class DynamicQrController extends Controller
{
    public function __construct(
        protected DynamicQrService $dynamic,
        protected QrScanAnalyticsService $analytics,
        protected SeoService $seo,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $codes = $user->dynamicQrCodes()->paginate(12);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'My Dynamic QR Codes — CalchubNepal',
            'description' => 'Manage dynamic QR codes, destinations, passwords and scan analytics.',
            'canonical' => route('account.qr-codes.index'),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.qr-codes.index', [
            'user' => $user,
            'codes' => $codes,
            'meta' => $meta,
        ]);
    }

    public function show(Request $request, QrCode $qrCode): View
    {
        $this->authorize('view', $qrCode);
        abort_unless($qrCode->is_dynamic, 404);

        $report = $this->analytics->report($qrCode);

        $meta = $this->seo->buildMeta(null, [
            'title' => ($qrCode->title ?: 'Dynamic QR').' — Analytics',
            'canonical' => route('account.qr-codes.show', $qrCode),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.qr-codes.show', [
            'user' => $request->user(),
            'qr' => $qrCode,
            'report' => $report,
            'meta' => $meta,
        ]);
    }

    public function edit(Request $request, QrCode $qrCode): View
    {
        $this->authorize('update', $qrCode);

        $meta = $this->seo->buildMeta(null, [
            'title' => 'Edit Dynamic QR — CalchubNepal',
            'canonical' => route('account.qr-codes.edit', $qrCode),
            'robots' => 'noindex,nofollow',
        ]);

        return view('account.qr-codes.edit', [
            'user' => $request->user(),
            'qr' => $qrCode,
            'meta' => $meta,
        ]);
    }

    public function update(DynamicQrUpdateRequest $request, QrCode $qrCode): RedirectResponse
    {
        try {
            $this->dynamic->update($qrCode, $request->validated());
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('account.qr-codes.show', $qrCode)
            ->with('status', 'qr-updated');
    }

    public function destroy(Request $request, QrCode $qrCode): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $qrCode);
        $this->dynamic->delete($qrCode);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('account.qr-codes.index')
            ->with('status', 'qr-deleted');
    }

    public function pause(Request $request, QrCode $qrCode): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $qrCode);
        $this->dynamic->pause($qrCode);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $qrCode->fresh()->status->value]);
        }

        return back()->with('status', 'qr-paused');
    }

    public function resume(Request $request, QrCode $qrCode): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $qrCode);
        try {
            $this->dynamic->resume($qrCode);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'status' => $qrCode->fresh()->status->value]);
        }

        return back()->with('status', 'qr-resumed');
    }
}
