<?php

namespace App\Http\Controllers\Web\Account;

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

    public function index(Request $request): View
    {
        $results = BreathHoldResult::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('account.breath-hold.index', compact('results'));
    }

    public function show(Request $request, BreathHoldResult $result): View|JsonResponse
    {
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);

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
            'image_url' => route('account.breath-hold.image', $result),
            'download_url' => route('account.breath-hold.download', $result),
            'view_url' => route('account.breath-hold.show', $result),
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
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);

        return $this->breathHold->downloadCertificate($result, asDownload: false);
    }

    public function download(Request $request, BreathHoldResult $result): Response
    {
        abort_unless((int) $result->user_id === (int) $request->user()->id, 403);

        return $this->breathHold->downloadCertificate($result, asDownload: true);
    }
}
