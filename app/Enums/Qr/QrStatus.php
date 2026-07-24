<?php

namespace App\Enums\Qr;

enum QrStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Expired => 'Expired',
        };
    }
}
