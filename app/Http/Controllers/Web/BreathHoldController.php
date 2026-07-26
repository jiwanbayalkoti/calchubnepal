<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BreathHoldResult;
use App\Services\BreathHold\BreathHoldService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class BreathHoldController extends Controller
{
    public function __construct(protected BreathHoldService $breathHold)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'duration_ms' => ['required', 'integer', 'min:500', 'max:600000'],
        ]);

        $result = $this->breathHold->createFromRequest($request, $request->user());

        $request->session()->put('breath_hold_claim_token', $result->claim_token);

        return response()->json([
            'message' => $result->user_id
                ? 'Result saved. Your certificate is ready.'
                : 'Result saved. Sign up or log in to claim your certificate.',
            'claim_token' => $result->claim_token,
            'band' => $result->band,
            'duration_seconds' => $result->duration_seconds,
            'requires_auth' => $result->user_id === null,
            'certificate_code' => $result->certificate_code,
            'certificate_url' => $result->hasCertificate()
                ? route('breath-hold.certificate', $result)
                : null,
            'image_url' => $result->hasCertificate()
                ? route('breath-hold.certificate.image', $result)
                : null,
            'download_url' => $result->hasCertificate()
                ? route('breath-hold.certificate.download', $result)
                : null,
            'account_url' => route('account.breath-hold.index'),
        ]);
    }

    public function claim(Request $request): JsonResponse
    {
        $request->validate([
            'claim_token' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        abort_unless($user, 401);

        $result = $this->breathHold->claimForUser($request->string('claim_token')->toString(), $user);
        $request->session()->forget('breath_hold_claim_token');

        return response()->json([
            'message' => 'Certificate ready.',
            'certificate_code' => $result->certificate_code,
            'certificate_url' => route('breath-hold.certificate', $result),
            'image_url' => route('breath-hold.certificate.image', $result),
            'download_url' => route('breath-hold.certificate.download', $result),
            'account_url' => route('account.breath-hold.index'),
            'redirect' => route('breath-hold.certificate', $result),
        ]);
    }

    public function show(Request $request, BreathHoldResult $result): View|JsonResponse
    {
        $this->authorizeCertificate($request, $result);

        if (! $result->hasCertificate()) {
            $this->breathHold->issueCertificate($result);
            $result->refresh();
        }

        $result->loadMissing('user');

        $payload = [
            'id' => $result->id,
            'certificate_code' => $result->certificate_code,
            'duration' => $result->formattedDuration(),
            'band' => $result->band,
            'band_label' => $result->bandLabel(),
            'band_range' => $result->bandRangeLabel(),
            'funny_title' => $result->funnyTitle(),
            'funny_subtitle' => $result->funnySubtitle(),
            'played_at' => $result->created_at?->format('M j, Y g:i A'),
            'image_url' => route('breath-hold.certificate.image', $result),
            'download_url' => route('breath-hold.certificate.download', $result),
            'view_url' => route('breath-hold.certificate', $result),
        ];

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json($payload);
        }

        return view('breath-hold.show', [
            'result' => $result,
            'imageUrl' => $payload['image_url'],
            'downloadUrl' => $payload['download_url'],
        ]);
    }

    public function image(Request $request, BreathHoldResult $result): Response
    {
        $this->authorizeCertificate($request, $result);

        return $this->breathHold->downloadCertificate($result, asDownload: false);
    }

    public function download(Request $request, BreathHoldResult $result): Response
    {
        $this->authorizeCertificate($request, $result);

        return $this->breathHold->downloadCertificate($result, asDownload: true);
    }

    protected function authorizeCertificate(Request $request, BreathHoldResult $result): void
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_unless(
            (int) $result->user_id === (int) $user->id || $user->canAccessAdmin(),
            403
        );
    }
}
