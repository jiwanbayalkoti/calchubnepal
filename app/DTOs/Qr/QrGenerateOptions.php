<?php

namespace App\DTOs\Qr;

use App\Enums\Qr\QrErrorCorrection;
use App\Enums\Qr\QrEyeStyle;
use App\Enums\Qr\QrFrameStyle;
use App\Enums\Qr\QrModuleStyle;
use App\Enums\Qr\QrOutputFormat;
use App\Enums\Qr\QrType;

final class QrGenerateOptions
{
    /**
     * @param  array<string, mixed>  $input
     */
    public function __construct(
        public readonly QrType $type,
        public readonly array $input,
        public readonly int $size,
        public readonly string $foreground,
        public readonly string $background,
        public readonly int $margin,
        public readonly QrErrorCorrection $errorCorrection,
        public readonly QrOutputFormat $format = QrOutputFormat::Png,
        public readonly string $payload = '',
        public readonly ?string $logoPath = null,
        public readonly int $logoSize = 64,
        public readonly QrModuleStyle $moduleStyle = QrModuleStyle::Square,
        public readonly QrEyeStyle $eyeStyle = QrEyeStyle::Square,
        public readonly QrFrameStyle $frameStyle = QrFrameStyle::None,
        public readonly string $frameLabel = '',
        public readonly bool $saveHistory = true,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $size = (int) ($data['size'] ?? 256);
        if (! in_array($size, [128, 256, 512, 1024], true)) {
            $size = 256;
        }

        $margin = max(0, min(64, (int) ($data['margin'] ?? 10)));
        $logoSize = max(24, min(200, (int) ($data['logo_size'] ?? 64)));
        $logoPath = isset($data['logo_path']) && is_string($data['logo_path']) && $data['logo_path'] !== ''
            ? $data['logo_path']
            : null;

        $error = QrErrorCorrection::tryFrom((string) ($data['error_correction'] ?? 'M'))
            ?? QrErrorCorrection::Medium;

        if ($logoPath !== null && $error === QrErrorCorrection::Low) {
            $error = QrErrorCorrection::High;
        }

        return new self(
            type: QrType::from((string) ($data['type'] ?? QrType::Url->value)),
            input: is_array($data['input'] ?? null) ? $data['input'] : [],
            size: $size,
            foreground: self::normalizeHex((string) ($data['foreground'] ?? '#0B6E4F')),
            background: self::normalizeHex((string) ($data['background'] ?? '#FFFFFF')),
            margin: $margin,
            errorCorrection: $error,
            format: QrOutputFormat::tryFrom((string) ($data['format'] ?? 'png'))
                ?? QrOutputFormat::Png,
            logoPath: $logoPath,
            logoSize: $logoSize,
            moduleStyle: QrModuleStyle::tryFrom((string) ($data['module_style'] ?? 'square'))
                ?? QrModuleStyle::Square,
            eyeStyle: QrEyeStyle::tryFrom((string) ($data['eye_style'] ?? 'square'))
                ?? QrEyeStyle::Square,
            frameStyle: QrFrameStyle::tryFrom((string) ($data['frame_style'] ?? 'none'))
                ?? QrFrameStyle::None,
            frameLabel: trim((string) ($data['frame_label'] ?? '')),
            saveHistory: filter_var($data['save_history'] ?? true, FILTER_VALIDATE_BOOLEAN),
        );
    }

    public function withPayload(string $payload): self
    {
        return $this->cloneWith(['payload' => $payload]);
    }

    public function withFormat(QrOutputFormat $format): self
    {
        return $this->cloneWith(['format' => $format]);
    }

    public function withLogoPath(?string $path): self
    {
        return $this->cloneWith(['logoPath' => $path]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function cloneWith(array $overrides): self
    {
        return new self(
            type: $overrides['type'] ?? $this->type,
            input: $overrides['input'] ?? $this->input,
            size: $overrides['size'] ?? $this->size,
            foreground: $overrides['foreground'] ?? $this->foreground,
            background: $overrides['background'] ?? $this->background,
            margin: $overrides['margin'] ?? $this->margin,
            errorCorrection: $overrides['errorCorrection'] ?? $this->errorCorrection,
            format: $overrides['format'] ?? $this->format,
            payload: $overrides['payload'] ?? $this->payload,
            logoPath: array_key_exists('logoPath', $overrides) ? $overrides['logoPath'] : $this->logoPath,
            logoSize: $overrides['logoSize'] ?? $this->logoSize,
            moduleStyle: $overrides['moduleStyle'] ?? $this->moduleStyle,
            eyeStyle: $overrides['eyeStyle'] ?? $this->eyeStyle,
            frameStyle: $overrides['frameStyle'] ?? $this->frameStyle,
            frameLabel: $overrides['frameLabel'] ?? $this->frameLabel,
            saveHistory: $overrides['saveHistory'] ?? $this->saveHistory,
        );
    }

    public static function normalizeHex(string $color): string
    {
        $color = trim($color);
        if (preg_match('/^#([A-Fa-f0-9]{6})$/', $color)) {
            return strtoupper($color);
        }
        if (preg_match('/^#([A-Fa-f0-9]{3})$/', $color, $m)) {
            $h = $m[1];

            return strtoupper('#'.$h[0].$h[0].$h[1].$h[1].$h[2].$h[2]);
        }

        return '#0B6E4F';
    }
}
