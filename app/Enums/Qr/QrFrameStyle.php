<?php

namespace App\Enums\Qr;

enum QrFrameStyle: string
{
    case None = 'none';
    case Simple = 'simple';
    case Banner = 'banner';
    case Card = 'card';

    public function label(): string
    {
        return match ($this) {
            self::None => 'No frame',
            self::Simple => 'Simple border',
            self::Banner => 'Bottom banner',
            self::Card => 'Card frame',
        };
    }
}
