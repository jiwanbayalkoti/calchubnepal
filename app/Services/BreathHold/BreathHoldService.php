<?php

namespace App\Services\BreathHold;

use App\Models\BreathHoldResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class BreathHoldService
{
    public function __construct(protected BreathHoldCertificateImageRenderer $certificateImage)
    {
    }

    public function resolveBand(float $seconds): string
    {
        if ($seconds < 20) {
            return 'poor';
        }

        if ($seconds < 40) {
            return 'medium';
        }

        return 'healthy';
    }

    public function createFromRequest(Request $request, ?User $user = null): BreathHoldResult
    {
        $durationMs = (int) $request->integer('duration_ms');
        $durationMs = max(500, min($durationMs, 600000)); // 0.5s .. 10min
        $seconds = round($durationMs / 1000, 2);
        $band = $this->resolveBand($seconds);

        $result = BreathHoldResult::query()->create([
            'user_id' => $user?->id,
            'claim_token' => Str::random(48),
            'duration_ms' => $durationMs,
            'duration_seconds' => $seconds,
            'band' => $band,
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip().'|'.(string) config('app.key')) : null,
            'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
        ]);

        if ($user) {
            $this->issueCertificate($result);
        }

        return $result->fresh();
    }

    public function claimForUser(string $claimToken, User $user): BreathHoldResult
    {
        return DB::transaction(function () use ($claimToken, $user) {
            /** @var BreathHoldResult $result */
            $result = BreathHoldResult::query()
                ->where('claim_token', $claimToken)
                ->lockForUpdate()
                ->firstOrFail();

            if ($result->user_id && (int) $result->user_id !== (int) $user->id) {
                abort(403, 'This result already belongs to another account.');
            }

            $result->user_id = $user->id;
            $result->save();

            if (! $result->hasCertificate()) {
                $this->issueCertificate($result);
            }

            return $result->fresh();
        });
    }

    public function claimPendingFromSession(Request $request, User $user): ?BreathHoldResult
    {
        $token = $request->session()->pull('breath_hold_claim_token');

        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            return $this->claimForUser($token, $user);
        } catch (\Throwable) {
            return null;
        }
    }

    public function issueCertificate(BreathHoldResult $result): BreathHoldResult
    {
        if ($result->hasCertificate()) {
            return $result;
        }

        do {
            $code = 'BH-'.strtoupper(Str::random(8));
        } while (BreathHoldResult::query()->where('certificate_code', $code)->exists());

        $result->forceFill([
            'certificate_code' => $code,
            'certificate_issued_at' => now(),
        ])->save();

        return $result->fresh();
    }

    public function downloadCertificate(BreathHoldResult $result, bool $asDownload = true): Response
    {
        if (! $result->hasCertificate()) {
            $this->issueCertificate($result);
            $result->refresh();
        }

        $png = $this->certificateImage->renderPng(
            $result,
            (string) config('app.name', 'Calculator Hub')
        );

        $filename = 'breath-hold-certificate-'.($result->certificate_code ?? $result->id).'.png';
        $disposition = ($asDownload ? 'attachment' : 'inline').'; filename="'.$filename.'"';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => $disposition,
            'Content-Length' => (string) strlen($png),
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * @return array{total:int, today:int, certificates:int, by_band:array<string,int>}
     */
    public function adminStats(): array
    {
        $byBand = BreathHoldResult::query()
            ->select('band', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('band')
            ->pluck('aggregate', 'band')
            ->map(fn ($v) => (int) $v)
            ->all();

        return [
            'total' => BreathHoldResult::query()->count(),
            'today' => BreathHoldResult::query()->whereDate('created_at', today())->count(),
            'certificates' => BreathHoldResult::query()->whereNotNull('certificate_code')->count(),
            'by_band' => [
                'poor' => (int) ($byBand['poor'] ?? 0),
                'medium' => (int) ($byBand['medium'] ?? 0),
                'healthy' => (int) ($byBand['healthy'] ?? 0),
            ],
        ];
    }
}
