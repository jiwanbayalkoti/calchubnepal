<?php

namespace App\Enums\Qr;

enum QrOutputFormat: string
{
    case Png = 'png';
    case Svg = 'svg';
    case Jpg = 'jpg';
    case Webp = 'webp';
    case Pdf = 'pdf';

    public function mimeType(): string
    {
        return match ($this) {
            self::Png => 'image/png',
            self::Svg => 'image/svg+xml',
            self::Jpg => 'image/jpeg',
            self::Webp => 'image/webp',
            self::Pdf => 'application/pdf',
        };
    }

    public function extension(): string
    {
        return $this->value;
    }

    public function supportsLogo(): bool
    {
        return $this !== self::Svg;
    }
}
