<?php

namespace App\Enums\Qr;

enum QrEyeStyle: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Leaf = 'leaf';

    public function label(): string
    {
        return match ($this) {
            self::Square => 'Square eyes',
            self::Rounded => 'Rounded eyes',
            self::Leaf => 'Leaf eyes',
        };
    }
}
