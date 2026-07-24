<?php

namespace App\Enums\Qr;

enum QrErrorCorrection: string
{
    case Low = 'L';
    case Medium = 'M';
    case Quartile = 'Q';
    case High = 'H';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'L — Low (~7%)',
            self::Medium => 'M — Medium (~15%)',
            self::Quartile => 'Q — Quartile (~25%)',
            self::High => 'H — High (~30%)',
        };
    }
}
