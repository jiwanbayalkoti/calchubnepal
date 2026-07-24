<?php

namespace App\Enums\Qr;

enum QrModuleStyle: string
{
    case Square = 'square';
    case Rounded = 'rounded';
    case Dots = 'dots';

    public function label(): string
    {
        return match ($this) {
            self::Square => 'Square',
            self::Rounded => 'Rounded',
            self::Dots => 'Dots',
        };
    }
}
