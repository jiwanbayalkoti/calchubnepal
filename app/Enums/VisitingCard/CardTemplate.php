<?php

namespace App\Enums\VisitingCard;

enum CardTemplate: string
{
    case Classic = 'classic';
    case Modern = 'modern';
    case Minimal = 'minimal';
    case Bold = 'bold';
    case Split = 'split';
    case Noir = 'noir';
    case Editorial = 'editorial';
    case Luxe = 'luxe';
    case Horizon = 'horizon';
    case Diagonal = 'diagonal';
    case Duotone = 'duotone';
    case Corner = 'corner';
    case Monogram = 'monogram';
    case Stack = 'stack';
    case Ribbon = 'ribbon';
    case Geometric = 'geometric';
    case Neon = 'neon';
    case Swiss = 'swiss';
    case Crest = 'crest';
    case Cascade = 'cascade';
    // New attractive layouts
    case Aurora = 'aurora';
    case Marble = 'marble';
    case Orbit = 'orbit';
    case Pulse = 'pulse';
    case Ledger = 'ledger';
    case Studio = 'studio';
    case Ink = 'ink';
    case Metro = 'metro';
    case Prism = 'prism';
    case Velvet = 'velvet';
    case Executive = 'executive';
    case Wave = 'wave';

    public function label(): string
    {
        return match ($this) {
            self::Classic => 'Classic',
            self::Modern => 'Modern',
            self::Minimal => 'Minimal',
            self::Bold => 'Bold Frame',
            self::Split => 'Brand Split',
            self::Noir => 'Noir Luxury',
            self::Editorial => 'Editorial Type',
            self::Luxe => 'Luxe Foil',
            self::Horizon => 'Horizon',
            self::Diagonal => 'Diagonal Cut',
            self::Duotone => 'Duotone',
            self::Corner => 'Corner Mark',
            self::Monogram => 'Monogram',
            self::Stack => 'Centered Stack',
            self::Ribbon => 'Side Ribbon',
            self::Geometric => 'Geometric',
            self::Neon => 'Neon Dark',
            self::Swiss => 'Swiss Grid',
            self::Crest => 'Crest Formal',
            self::Cascade => 'Cascade Bands',
            self::Aurora => 'Aurora Glow',
            self::Marble => 'Marble Gold',
            self::Orbit => 'Orbit',
            self::Pulse => 'Pulse Bars',
            self::Ledger => 'Ledger',
            self::Studio => 'Studio Panel',
            self::Ink => 'Ink Initial',
            self::Metro => 'Metro Bar',
            self::Prism => 'Prism',
            self::Velvet => 'Velvet Night',
            self::Executive => 'Executive',
            self::Wave => 'Wave Edge',
        };
    }

    public function category(): string
    {
        return match ($this) {
            self::Classic, self::Modern, self::Horizon, self::Ribbon, self::Swiss, self::Metro
                => 'Professional',
            self::Minimal, self::Editorial, self::Stack, self::Corner, self::Crest, self::Ink
                => 'Minimal',
            self::Bold, self::Split, self::Diagonal, self::Duotone, self::Cascade, self::Prism
                => 'Bold',
            self::Noir, self::Luxe, self::Monogram, self::Neon, self::Geometric, self::Marble, self::Velvet
                => 'Premium',
            self::Aurora, self::Orbit, self::Studio, self::Wave
                => 'Creative',
            self::Pulse, self::Ledger, self::Executive
                => 'Corporate',
        };
    }

    /**
     * Filter chips order for the designer UI.
     *
     * @return list<string>
     */
    public static function categories(): array
    {
        return ['Professional', 'Minimal', 'Bold', 'Premium', 'Creative', 'Corporate'];
    }

    /**
     * Suggested brand colors for a polished first look.
     *
     * @return array{primary: string, secondary: string, text: string, background: string}
     */
    public function defaultColors(): array
    {
        return match ($this) {
            self::Classic => ['primary' => '#0B6E4F', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Modern => ['primary' => '#0B6E4F', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Minimal => ['primary' => '#1A1A1A', 'secondary' => '#C4A574', 'text' => '#1A1A1A', 'background' => '#FAFAF8'],
            self::Bold => ['primary' => '#0B6E4F', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Split => ['primary' => '#123C2E', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Noir => ['primary' => '#D4AF37', 'secondary' => '#8B7355', 'text' => '#F5F0E8', 'background' => '#121212'],
            self::Editorial => ['primary' => '#111111', 'secondary' => '#E4572E', 'text' => '#111111', 'background' => '#FFFFFF'],
            self::Luxe => ['primary' => '#B08D57', 'secondary' => '#2C2C2C', 'text' => '#2C2C2C', 'background' => '#F7F3EA'],
            self::Horizon => ['primary' => '#0E4D6C', 'secondary' => '#F2C14E', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Diagonal => ['primary' => '#0B6E4F', 'secondary' => '#163A2C', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Duotone => ['primary' => '#1B3A4B', 'secondary' => '#E8D5B5', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Corner => ['primary' => '#0B6E4F', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Monogram => ['primary' => '#1F3A5F', 'secondary' => '#C9A227', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Stack => ['primary' => '#0B6E4F', 'secondary' => '#9CA3AF', 'text' => '#111827', 'background' => '#FFFFFF'],
            self::Ribbon => ['primary' => '#7A1F2B', 'secondary' => '#D4A373', 'text' => '#1A1A1A', 'background' => '#FFFCF8'],
            self::Geometric => ['primary' => '#0F766E', 'secondary' => '#134E4A', 'text' => '#ECFDF5', 'background' => '#042F2E'],
            self::Neon => ['primary' => '#39FF14', 'secondary' => '#00E5FF', 'text' => '#E8FFE8', 'background' => '#0A0A0A'],
            self::Swiss => ['primary' => '#E10600', 'secondary' => '#111111', 'text' => '#111111', 'background' => '#FFFFFF'],
            self::Crest => ['primary' => '#1C2A44', 'secondary' => '#C5A572', 'text' => '#1C2A44', 'background' => '#FBF8F1'],
            self::Cascade => ['primary' => '#0B6E4F', 'secondary' => '#2A9D8F', 'text' => '#FFFFFF', 'background' => '#0B6E4F'],
            self::Aurora => ['primary' => '#5B2C6F', 'secondary' => '#1ABC9C', 'text' => '#FFFFFF', 'background' => '#1A0B2E'],
            self::Marble => ['primary' => '#C9A227', 'secondary' => '#8B7355', 'text' => '#2C2416', 'background' => '#F4EFE6'],
            self::Orbit => ['primary' => '#2563EB', 'secondary' => '#F59E0B', 'text' => '#0F172A', 'background' => '#FFFFFF'],
            self::Pulse => ['primary' => '#0F766E', 'secondary' => '#14B8A6', 'text' => '#134E4A', 'background' => '#F0FDFA'],
            self::Ledger => ['primary' => '#1E3A5F', 'secondary' => '#94A3B8', 'text' => '#0F172A', 'background' => '#FFFFFF'],
            self::Studio => ['primary' => '#EA580C', 'secondary' => '#1C1917', 'text' => '#1C1917', 'background' => '#FFFAF5'],
            self::Ink => ['primary' => '#111827', 'secondary' => '#9CA3AF', 'text' => '#111827', 'background' => '#FFFFFF'],
            self::Metro => ['primary' => '#0B6E4F', 'secondary' => '#F4A259', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Prism => ['primary' => '#7C3AED', 'secondary' => '#F43F5E', 'text' => '#1A1A1A', 'background' => '#FFFFFF'],
            self::Velvet => ['primary' => '#E8B4B8', 'secondary' => '#F5D0C5', 'text' => '#FDF2F8', 'background' => '#3B0A2E'],
            self::Executive => ['primary' => '#1C2A44', 'secondary' => '#C5A572', 'text' => '#1C2A44', 'background' => '#FFFFFF'],
            self::Wave => ['primary' => '#0284C7', 'secondary' => '#38BDF8', 'text' => '#FFFFFF', 'background' => '#0C4A6E'],
        };
    }

    /**
     * @return array{x: string, y: string, size: int}
     */
    public function logoAnchor(): array
    {
        return match ($this) {
            self::Split, self::Ribbon, self::Diagonal, self::Studio => ['x' => 'left', 'y' => 'middle', 'size' => 100],
            self::Classic, self::Horizon, self::Metro, self::Ledger => ['x' => 'left', 'y' => 'top', 'size' => 96],
            self::Modern, self::Duotone, self::Pulse => ['x' => 'right', 'y' => 'top', 'size' => 88],
            self::Monogram, self::Crest, self::Stack, self::Orbit, self::Ink => ['x' => 'center', 'y' => 'top', 'size' => 92],
            self::Noir, self::Neon, self::Geometric, self::Velvet, self::Aurora => ['x' => 'right', 'y' => 'top', 'size' => 90],
            self::Cascade, self::Wave => ['x' => 'right', 'y' => 'bottom', 'size' => 88],
            self::Executive, self::Marble => ['x' => 'right', 'y' => 'top', 'size' => 88],
            default => ['x' => 'right', 'y' => 'top', 'size' => 100],
        };
    }

    /**
     * @return array{x: string, y: string, size: int}
     */
    public function qrAnchor(): array
    {
        return match ($this) {
            self::Split, self::Ribbon, self::Studio => ['x' => 'right', 'y' => 'bottom', 'size' => 130],
            self::Stack, self::Crest, self::Monogram, self::Orbit, self::Ink => ['x' => 'right', 'y' => 'bottom', 'size' => 120],
            self::Duotone, self::Pulse => ['x' => 'left', 'y' => 'bottom', 'size' => 130],
            self::Cascade, self::Wave => ['x' => 'left', 'y' => 'bottom', 'size' => 120],
            default => ['x' => 'right', 'y' => 'bottom', 'size' => 140],
        };
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * @return array{value: string, label: string, category: string, colors: array{primary: string, secondary: string, text: string, background: string}}
     */
    public function toOption(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'category' => $this->category(),
            'colors' => $this->defaultColors(),
        ];
    }
}
