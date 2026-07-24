<?php

namespace App\Services\Qr;

use App\Contracts\Qr\QrCodeRepositoryInterface;
use App\Enums\Qr\QrOutputFormat;
use App\Enums\Qr\QrStatus;
use App\Models\QrCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Phase 3 — Dynamic QR platform (editable destination, short URL, password, expiry).
 */
class DynamicQrService
{
    public function __construct(
        protected QrGeneratorService $generator,
        protected QrCodeRepositoryInterface $repository,
        protected QrScanAnalyticsService $analytics,
    ) {
    }

    /**
     * Create a dynamic QR that encodes the short URL, not the destination.
     *
     * @param  array<string, mixed>  $data
     * @return array{qr: QrCode, result: \App\DTOs\Qr\QrGenerateResult, short_url: string, image: string}
     */
    public function create(array $data, ?UploadedFile $logo = null, ?int $userId = null): array
    {
        $destination = $this->normalizeDestination((string) ($data['destination_url'] ?? $data['input']['url'] ?? ''));
        if ($destination === '') {
            throw new InvalidArgumentException('A destination URL is required for dynamic QR codes.');
        }

        $userId ??= Auth::id();
        if (! $userId) {
            throw new InvalidArgumentException('Please sign in to create dynamic QR codes.');
        }

        $user = \App\Models\User::query()->find($userId);
        if ($user) {
            $entitlements = app(QrEntitlementService::class);
            $count = $user->dynamicQrCodes()->count();
            $max = $entitlements->maxDynamicQr($user);
            if ($count >= $max) {
                throw new InvalidArgumentException("Dynamic QR limit reached ({$max}). Upgrade your plan for more.");
            }
        }

        $shortCode = $this->uniqueShortCode();
        $shortUrl = url('/q/'.$shortCode);

        $generateData = $data;
        $generateData['type'] = 'url';
        $generateData['input'] = ['url' => $shortUrl];
        $generateData['save_history'] = false;

        $result = $this->generator->generate($generateData, QrOutputFormat::Png, $logo, persist: false);

        $previewPath = 'qr-previews/'.Str::uuid().'.png';
        Storage::disk('public')->put($previewPath, $result->binary);

        $password = $data['password'] ?? null;
        $expiresAt = $data['expires_at'] ?? null;

        $qr = $this->repository->store([
            'user_id' => $userId,
            'session_id' => session()->getId(),
            'short_code' => $shortCode,
            'type' => 'url',
            'payload' => $shortUrl,
            'destination_url' => $destination,
            'input_json' => array_merge(is_array($data['input'] ?? null) ? $data['input'] : [], [
                'destination_url' => $destination,
            ]),
            'style_json' => [
                'size' => (int) ($generateData['size'] ?? 256),
                'foreground' => (string) ($generateData['foreground'] ?? '#0B6E4F'),
                'background' => (string) ($generateData['background'] ?? '#FFFFFF'),
                'margin' => (int) ($generateData['margin'] ?? 10),
                'error_correction' => (string) ($generateData['error_correction'] ?? 'M'),
                'module_style' => (string) ($generateData['module_style'] ?? 'square'),
                'eye_style' => (string) ($generateData['eye_style'] ?? 'square'),
                'frame_style' => (string) ($generateData['frame_style'] ?? 'none'),
                'frame_label' => (string) ($generateData['frame_label'] ?? ''),
                'format' => 'png',
            ],
            'title' => (string) ($data['title'] ?? $this->guessTitle($destination)),
            'is_saved' => true,
            'is_dynamic' => true,
            'status' => QrStatus::Active->value,
            'password_hash' => filled($password) ? Hash::make((string) $password) : null,
            'expires_at' => filled($expiresAt) ? $expiresAt : null,
            'scan_count' => 0,
            'preview_path' => $previewPath,
            'workspace_id' => $data['workspace_id'] ?? null,
            'campaign_id' => $data['campaign_id'] ?? null,
            'brand_template_id' => $data['brand_template_id'] ?? null,
        ]);

        return [
            'qr' => $qr,
            'result' => $result,
            'short_url' => $shortUrl,
            'image' => $result->dataUri(),
        ];
    }

    /**
     * Update editable fields without regenerating the short URL / QR payload.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(QrCode $qrCode, array $data): QrCode
    {
        if (! $qrCode->is_dynamic) {
            throw new InvalidArgumentException('Only dynamic QR codes can be edited.');
        }

        $updates = [];

        if (array_key_exists('title', $data)) {
            $updates['title'] = filled($data['title']) ? (string) $data['title'] : null;
        }

        if (array_key_exists('destination_url', $data) && filled($data['destination_url'])) {
            $updates['destination_url'] = $this->normalizeDestination((string) $data['destination_url']);
            $input = $qrCode->input_json ?? [];
            $input['destination_url'] = $updates['destination_url'];
            $updates['input_json'] = $input;
        }

        if (array_key_exists('expires_at', $data)) {
            $updates['expires_at'] = filled($data['expires_at']) ? $data['expires_at'] : null;
        }

        if (array_key_exists('status', $data) && filled($data['status'])) {
            $status = QrStatus::tryFrom((string) $data['status']);
            if ($status) {
                $updates['status'] = $status->value;
            }
        }

        if (array_key_exists('password', $data)) {
            $password = $data['password'];
            if ($password === null || $password === '') {
                // leave unchanged unless remove_password
            } else {
                $updates['password_hash'] = Hash::make((string) $password);
            }
        }

        if (array_key_exists('remove_password', $data) && filter_var($data['remove_password'], FILTER_VALIDATE_BOOLEAN)) {
            $updates['password_hash'] = null;
        }

        if ($updates !== []) {
            $qrCode->update($updates);
        }

        $qrCode = $qrCode->refresh();
        if ($qrCode->isExpired() && $qrCode->status !== QrStatus::Expired) {
            $qrCode->update(['status' => QrStatus::Expired->value]);
            $qrCode = $qrCode->refresh();
        }

        $this->analytics->forgetCache($qrCode);

        return $qrCode;
    }

    public function pause(QrCode $qrCode): QrCode
    {
        return $this->update($qrCode, ['status' => QrStatus::Paused->value]);
    }

    public function resume(QrCode $qrCode): QrCode
    {
        if ($qrCode->isExpired()) {
            throw new InvalidArgumentException('Expired QR codes cannot be resumed. Extend the expiry date first.');
        }

        return $this->update($qrCode, ['status' => QrStatus::Active->value]);
    }

    public function findByShortCode(string $code): ?QrCode
    {
        return $this->repository->findByShortCode($code);
    }

    public function delete(QrCode $qrCode): bool
    {
        if ($qrCode->preview_path) {
            Storage::disk('public')->delete($qrCode->preview_path);
        }

        return (bool) $qrCode->delete();
    }

    public function uniqueShortCode(int $length = 8): string
    {
        do {
            $code = Str::lower(Str::random($length));
        } while ($this->repository->findByShortCode($code));

        return $code;
    }

    public function normalizeDestination(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (! preg_match('#^https?://#i', $url)) {
            $url = 'https://'.$url;
        }
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Please provide a valid destination URL.');
        }

        return $url;
    }

    protected function guessTitle(string $destination): string
    {
        $host = parse_url($destination, PHP_URL_HOST);

        return $host ? 'Dynamic: '.$host : 'Dynamic QR';
    }
}
