<?php

namespace App\Http\Controllers\Web;

use App\Enums\Qr\QrStatus;
use App\Http\Controllers\Controller;
use App\Models\QrCode;
use App\Services\Qr\DynamicQrService;
use App\Services\Qr\QrScanAnalyticsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public short-URL redirect endpoint for Phase 3 dynamic QRs.
 */
class QrRedirectController extends Controller
{
    public function __construct(
        protected DynamicQrService $dynamic,
        protected QrScanAnalyticsService $analytics,
    ) {
    }

    public function __invoke(Request $request, string $code): View|RedirectResponse
    {
        $qr = $this->dynamic->findByShortCode($code);
        if (! $qr) {
            abort(404, 'QR code not found.');
        }

        if ($qr->isExpired()) {
            if ($qr->status !== QrStatus::Expired) {
                $qr->update(['status' => QrStatus::Expired->value]);
            }

            return view('qr-code-generator.redirect-blocked', [
                'qr' => $qr->loadMissing('workspace'),
                'reason' => 'expired',
                'title' => 'This QR code has expired',
                'message' => 'The owner set an expiry date that has passed.',
            ]);
        }

        if ($qr->isPaused()) {
            return view('qr-code-generator.redirect-blocked', [
                'qr' => $qr->loadMissing('workspace'),
                'reason' => 'paused',
                'title' => 'This QR code is paused',
                'message' => 'The owner temporarily disabled this QR code.',
            ]);
        }

        if ($qr->isPasswordProtected() && ! $this->isUnlocked($request, $qr)) {
            return view('qr-code-generator.unlock', [
                'qr' => $qr->loadMissing('workspace'),
                'code' => $code,
            ]);
        }

        if (! $qr->isRedirectable()) {
            return view('qr-code-generator.redirect-blocked', [
                'qr' => $qr->loadMissing('workspace'),
                'reason' => 'inactive',
                'title' => 'This QR code is unavailable',
                'message' => 'The destination is missing or the QR is inactive.',
            ]);
        }

        $this->analytics->record($qr, $request);
        $this->analytics->forgetCache($qr);

        return redirect()->away($qr->destination_url, 302);
    }

    public function unlockForm(string $code): View
    {
        $qr = $this->dynamic->findByShortCode($code);
        if (! $qr) {
            abort(404);
        }

        return view('qr-code-generator.unlock', [
            'qr' => $qr,
            'code' => $code,
        ]);
    }

    public function unlock(Request $request, string $code): RedirectResponse|View
    {
        $qr = $this->dynamic->findByShortCode($code);
        if (! $qr) {
            abort(404);
        }

        $request->validate([
            'password' => ['required', 'string', 'max:100'],
        ]);

        if (! $qr->checkPassword((string) $request->input('password'))) {
            return view('qr-code-generator.unlock', [
                'qr' => $qr,
                'code' => $code,
                'error' => 'Incorrect password. Please try again.',
            ]);
        }

        $request->session()->put($this->unlockKey($qr), true);

        return redirect()->route('qr.redirect', ['code' => $code]);
    }

    protected function isUnlocked(Request $request, QrCode $qr): bool
    {
        return (bool) $request->session()->get($this->unlockKey($qr), false);
    }

    protected function unlockKey(QrCode $qr): string
    {
        return 'qr_unlocked_'.$qr->id;
    }
}
