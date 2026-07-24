<?php

namespace App\Services\Qr;

use App\Contracts\Qr\QrCodeGeneratorInterface;
use App\Contracts\Qr\QrCodeRepositoryInterface;
use App\DTOs\Qr\QrGenerateOptions;
use App\DTOs\Qr\QrGenerateResult;
use App\Enums\Qr\QrOutputFormat;
use App\Models\QrCode;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QrGeneratorService
{
    public function __construct(
        protected QrTypeRegistry $registry,
        protected QrCodeGeneratorInterface $generator,
        protected QrCodeRepositoryInterface $repository,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function preview(array $data, ?UploadedFile $logo = null, bool $persist = false): QrGenerateResult
    {
        return $this->generate($data, QrOutputFormat::Png, $logo, persist: $persist);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function download(array $data, QrOutputFormat $format, ?UploadedFile $logo = null): QrGenerateResult
    {
        return $this->generate($data, $format, $logo, persist: true);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function generate(
        array $data,
        ?QrOutputFormat $format = null,
        ?UploadedFile $logo = null,
        bool $persist = false,
    ): QrGenerateResult {
        $options = QrGenerateOptions::fromArray($data);
        if ($format !== null) {
            $options = $options->withFormat($format);
        }

        $tempLogo = null;
        if ($logo instanceof UploadedFile) {
            $tempLogo = $this->storeTempLogo($logo);
            $options = $options->withLogoPath($tempLogo);
        } elseif (! empty($data['logo_token']) && is_string($data['logo_token'])) {
            $resolved = $this->resolveLogoToken($data['logo_token']);
            if ($resolved) {
                $options = $options->withLogoPath($resolved);
            }
        }

        $payload = $this->registry->get($options->type)->build($options->input);
        $result = $this->generator->generate($options->withPayload($payload));

        if ($persist && ($options->saveHistory ?? true)) {
            $this->persistHistory($options->withPayload($payload), $result);
        }

        return $result;
    }

    protected function persistHistory(QrGenerateOptions $options, QrGenerateResult $result): ?QrCode
    {
        $previewPath = null;
        if ($result->format === QrOutputFormat::Png || str_starts_with($result->mimeType, 'image/')) {
            $previewPath = 'qr-previews/'.Str::uuid().'.png';
            // Store preview as PNG when possible
            $binary = $result->binary;
            if ($result->format !== QrOutputFormat::Png) {
                // keep original for non-png; skip heavy conversion for history thumb
                $previewPath = 'qr-previews/'.Str::uuid().'.'.$result->format->extension();
            }
            Storage::disk('public')->put($previewPath, $binary);
        }

        return $this->repository->store([
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'type' => $options->type->value,
            'payload' => $result->payload,
            'input_json' => $options->input,
            'style_json' => [
                'size' => $options->size,
                'foreground' => $options->foreground,
                'background' => $options->background,
                'margin' => $options->margin,
                'error_correction' => $options->errorCorrection->value,
                'module_style' => $options->moduleStyle->value,
                'eye_style' => $options->eyeStyle->value,
                'frame_style' => $options->frameStyle->value,
                'frame_label' => $options->frameLabel,
                'format' => $result->format->value,
            ],
            'title' => $this->guessTitle($options),
            'is_saved' => false,
            'preview_path' => $previewPath,
        ]);
    }

    protected function guessTitle(QrGenerateOptions $options): string
    {
        $input = $options->input;

        return match ($options->type->value) {
            'url' => (string) ($input['url'] ?? 'Website QR'),
            'text' => mb_substr((string) ($input['text'] ?? 'Text QR'), 0, 60),
            'email' => (string) ($input['email'] ?? 'Email QR'),
            'wifi' => 'WiFi: '.(string) ($input['ssid'] ?? ''),
            'vcard' => trim(($input['first_name'] ?? '').' '.($input['last_name'] ?? '')) ?: 'vCard',
            'event' => (string) ($input['title'] ?? 'Event'),
            'social' => (string) ($input['username'] ?? $input['url'] ?? 'Social'),
            'maps' => (string) ($input['query'] ?? 'Map'),
            'bank' => trim(($input['bank_name'] ?? '').' · '.($input['account_name'] ?? '')) ?: 'Bank Details',
            'telegram' => (string) ($input['username'] ?? $input['phone'] ?? 'Telegram'),
            'viber' => (string) ($input['phone'] ?? 'Viber'),
            'messenger' => (string) ($input['username'] ?? $input['page_id'] ?? 'Messenger'),
            'mecard' => (string) ($input['name'] ?? 'MeCard'),
            'app' => (string) ($input['url'] ?? $input['ios_url'] ?? $input['android_url'] ?? 'App'),
            'pdf', 'image', 'music' => (string) ($input['url'] ?? $options->type->label()),
            'crypto' => (string) ($input['coin'] ?? 'Crypto').' wallet',
            'esewa' => 'eSewa: '.(string) ($input['esewa_id'] ?? ''),
            'khalti' => 'Khalti: '.(string) ($input['khalti_id'] ?? ''),
            'upi' => 'UPI: '.(string) ($input['pa'] ?? ''),
            'nepal_qr' => (string) ($input['merchant_name'] ?? 'Nepal QR'),
            'calendar' => (string) ($input['title'] ?? 'Calendar'),
            'meeting' => (string) ($input['platform'] ?? 'Meeting'),
            'review' => 'Review',
            'coupon' => 'Promo: '.(string) ($input['code'] ?? ''),
            'multi_url' => (string) ($input['title'] ?? 'Multi URL'),
            default => $options->type->label(),
        };
    }

    public function storeLogo(UploadedFile $logo): string
    {
        $token = (string) Str::uuid();
        $ext = strtolower($logo->getClientOriginalExtension() ?: 'png');
        if (! in_array($ext, ['png', 'jpg', 'jpeg', 'webp', 'gif'], true)) {
            $ext = 'png';
        }
        Storage::disk('local')->putFileAs('qr-logos', $logo, "{$token}.{$ext}");

        return $token;
    }

    public function resolveLogoToken(string $token): ?string
    {
        if (! preg_match('/^[a-f0-9\-]{36}$/i', $token)) {
            return null;
        }

        foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
            $relative = "qr-logos/{$token}.{$ext}";
            if (Storage::disk('local')->exists($relative)) {
                return Storage::disk('local')->path($relative);
            }
        }

        return null;
    }

    protected function storeTempLogo(UploadedFile $logo): string
    {
        $token = $this->storeLogo($logo);
        $path = $this->resolveLogoToken($token);
        if (! $path) {
            throw new \InvalidArgumentException('Unable to store logo upload.');
        }

        return $path;
    }

    public function types(): QrTypeRegistry
    {
        return $this->registry;
    }

    public function repository(): QrCodeRepositoryInterface
    {
        return $this->repository;
    }
}
