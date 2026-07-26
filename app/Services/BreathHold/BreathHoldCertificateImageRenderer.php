<?php

namespace App\Services\BreathHold;

use App\Models\BreathHoldResult;
use InvalidArgumentException;

/**
 * Single-page funny Breath Hold certificate as PNG (GD).
 */
class BreathHoldCertificateImageRenderer
{
    public const WIDTH = 1400;

    public const HEIGHT = 900;

    public function renderPng(BreathHoldResult $result, string $siteName = 'Calculator Hub'): string
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($img === false) {
            throw new InvalidArgumentException('Unable to create certificate canvas.');
        }

        imagealphablending($img, true);
        imagesavealpha($img, false);

        $result->loadMissing('user');
        $palette = $result->certificatePalette();
        $c = $this->colors($img, $palette);

        // Cream background
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['cream']);

        // Outer + dashed-style inner borders
        $this->rect($img, 18, 18, self::WIDTH - 19, self::HEIGHT - 19, $c['primary'], 10);
        $this->rect($img, 34, 34, self::WIDTH - 35, self::HEIGHT - 35, $c['accent'], 3);

        // Decorative blobs
        $this->filledCircle($img, self::WIDTH - 80, 70, 90, $c['blob']);
        $this->filledCircle($img, 60, self::HEIGHT - 80, 70, $c['blob2']);
        $this->filledCircle($img, (int) (self::WIDTH * 0.42), 140, 40, $c['blob']);

        // Header
        $this->text($img, 18, 58, 52, $siteName, $c['primary']);
        $this->text($img, 11, 58, 78, 'BUREAU OF BREATH & GIGGLES', $c['muted']);

        // Stamp (top-right)
        $this->drawStamp($img, $result->funnyStamp(), $c);

        // Titles
        $this->centered($img, 12, 108, 'OFFICIALLY SILLY CERTIFICATE OF ACHIEVEMENT', $c['muted']);
        $this->centered($img, 36, 128, 'Breath Hold Test', $c['primary']);
        $this->centered($img, 14, 172, '*  *  *  *  *', $c['accent']);
        $this->centered($img, 22, 192, $result->funnyTitle(), $c['text']);
        $this->centered($img, 13, 224, $result->funnySubtitle(), $c['muted']);
        $this->centered($img, 15, 250, $result->funnyMotto(), $c['primary']);

        // Name card
        $name = $result->user?->name ?: 'Mysterious Breath Ninja';
        $email = (string) ($result->user?->email ?? '');
        $cardX1 = 220;
        $cardX2 = self::WIDTH - 220;
        $cardY1 = 278;
        $cardY2 = 358;
        imagefilledrectangle($img, $cardX1, $cardY1, $cardX2, $cardY2, $c['soft']);
        $this->rect($img, $cardX1, $cardY1, $cardX2, $cardY2, $c['accent'], 2);
        $this->centered($img, 11, 288, 'PROUDLY AWARDED TO', $c['muted']);
        $this->centered($img, 28, 308, $name, $c['text']);
        if ($email !== '') {
            $this->centered($img, 12, 340, $email, $c['muted']);
        }

        // Metric boxes
        $playedAt = $result->created_at ?? $result->certificate_issued_at ?? now();
        $issuedAt = $result->certificate_issued_at ?? now();
        $seconds = number_format((float) $result->duration_seconds, 2);

        $metrics = [
            ['Hold Time', $result->formattedDuration(), $seconds.' exact sec'],
            ['Result Band', $result->bandLabel(), $result->bandRangeLabel()],
            ['Fun Rank', $this->shortTitle($result->funnyTitle()), 'Totally unofficial'],
            ['Certificate ID', (string) $result->certificate_code, 'Fridge-worthy'],
        ];

        $boxW = 280;
        $gap = 18;
        $startX = (int) ((self::WIDTH - (($boxW * 4) + ($gap * 3))) / 2);
        $boxY = 378;
        $boxH = 100;

        foreach ($metrics as $i => [$label, $value, $note]) {
            $x = $startX + ($i * ($boxW + $gap));
            imagefilledrectangle($img, $x, $boxY, $x + $boxW, $boxY + $boxH, $c['badge']);
            $this->rect($img, $x, $boxY, $x + $boxW, $boxY + $boxH, $c['accent'], 1);
            $this->centeredInBox($img, 10, $x, $boxW, $boxY + 12, strtoupper($label), $c['muted']);
            $this->centeredInBox($img, strlen($value) > 18 ? 14 : 18, $x, $boxW, $boxY + 38, $value, $c['primary']);
            $this->centeredInBox($img, 10, $x, $boxW, $boxY + 72, $note, $c['muted']);
        }

        // Detail row
        $detailY = 500;
        $this->text($img, 11, 70, $detailY, 'Played: '.$playedAt->format('M j, Y g:i A'), $c['text']);
        $this->text($img, 11, 520, $detailY, 'Issued: '.$issuedAt->format('M j, Y g:i A'), $c['text']);
        $this->text($img, 11, 960, $detailY, 'Duration: '.number_format((int) $result->duration_ms).' ms', $c['text']);

        // Silly scale bar
        $scaleY = 540;
        $this->text($img, 10, 70, $scaleY, 'WHERE YOU LANDED ON THE SILLY SCALE', $c['muted']);
        $barX = 70;
        $barY = 565;
        $barW = self::WIDTH - 140;
        $barH = 22;
        $seg = (int) floor($barW / 3);
        imagefilledrectangle($img, $barX, $barY, $barX + $seg, $barY + $barH, $c['segPoor']);
        imagefilledrectangle($img, $barX + $seg, $barY, $barX + (2 * $seg), $barY + $barH, $c['segMedium']);
        imagefilledrectangle($img, $barX + (2 * $seg), $barY, $barX + $barW, $barY + $barH, $c['segHealthy']);
        $this->rect($img, $barX, $barY, $barX + $barW, $barY + $barH, $c['muted'], 1);

        $this->text($img, 10, $barX, $barY + 30, '0-20s Poor', $c['muted']);
        $this->text($img, 10, $barX + $seg + 40, $barY + 30, '20-40s Medium', $c['muted']);
        $this->text($img, 10, $barX + (2 * $seg) + 60, $barY + 30, '40s+ Healthy', $c['muted']);

        $markerLabel = 'YOU: '.$result->formattedDuration().'  →  '.$result->bandLabel();
        $this->text($img, 13, 70, 620, $markerLabel, $c['primary']);

        // Signatures
        $sigY = 700;
        imageline($img, 160, $sigY, 480, $sigY, $c['line']);
        imageline($img, 920, $sigY, 1240, $sigY, $c['line']);
        $this->centeredInBox($img, 13, 160, 320, $sigY + 10, 'Dr. Puffalot', $c['text']);
        $this->centeredInBox($img, 10, 160, 320, $sigY + 32, 'Chief Lung Officer (honorary)', $c['muted']);
        $this->centeredInBox($img, 13, 920, 320, $sigY + 10, 'Captain Airpause', $c['text']);
        $this->centeredInBox($img, 10, 920, 320, $sigY + 32, 'Director of Dramatic Inhales', $c['muted']);

        // Footer
        imageline($img, 70, 790, self::WIDTH - 70, 790, $c['line']);
        $this->centered($img, 10, 805, 'Fun / educational only — not a medical diagnosis, diving license, or party-stunt permit.', $c['muted']);
        $this->centered(
            $img,
            10,
            828,
            'Verify '.$result->certificate_code.'  ·  Generated '.$issuedAt->format('Y-m-d H:i').'  ·  '.$siteName,
            $c['muted']
        );

        ob_start();
        imagepng($img, null, 6);
        $binary = (string) ob_get_clean();
        imagedestroy($img);

        return $binary;
    }

    /**
     * @param  array{primary:string,accent:string,soft:string,badge:string}  $palette
     * @return array<string, int>
     */
    protected function colors($img, array $palette): array
    {
        return [
            'cream' => $this->hex($img, '#FFFEF8'),
            'text' => $this->hex($img, '#111827'),
            'muted' => $this->hex($img, '#6B7280'),
            'line' => $this->hex($img, '#9CA3AF'),
            'primary' => $this->hex($img, $palette['primary']),
            'accent' => $this->hex($img, $palette['accent']),
            'soft' => $this->hex($img, $palette['soft']),
            'badge' => $this->hex($img, $palette['badge']),
            'blob' => $this->hexAlpha($img, $palette['accent'], 40),
            'blob2' => $this->hexAlpha($img, $palette['primary'], 35),
            'segPoor' => $this->hex($img, '#FCA5A5'),
            'segMedium' => $this->hex($img, '#FCD34D'),
            'segHealthy' => $this->hex($img, '#86EFAC'),
            'stampBg' => $this->hex($img, $palette['badge']),
            'white' => $this->hex($img, '#FFFFFF'),
        ];
    }

    /** @param  \GdImage  $img */
    protected function drawStamp($img, string $label, array $c): void
    {
        $x = self::WIDTH - 250;
        $y = 48;
        $w = 170;
        $h = 52;
        imagefilledrectangle($img, $x, $y, $x + $w, $y + $h, $c['stampBg']);
        $this->rect($img, $x, $y, $x + $w, $y + $h, $c['primary'], 3);
        $this->centeredInBox($img, 12, $x, $w, $y + 16, $label, $c['primary']);
    }

    protected function shortTitle(string $title): string
    {
        return strlen($title) > 22 ? rtrim(substr($title, 0, 20)).'…' : $title;
    }

    /** @param  \GdImage  $img */
    protected function hex($img, string $hex): int
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (int) imagecolorallocate($img, $r, $g, $b);
    }

    /** @param  \GdImage  $img */
    protected function hexAlpha($img, string $hex, int $alpha): int
    {
        // GD alpha: 0 opaque .. 127 transparent
        $a = max(0, min(127, (int) round((100 - $alpha) * 1.27)));
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (int) imagecolorallocatealpha($img, $r, $g, $b, $a);
    }

    /** @param  \GdImage  $img */
    protected function rect($img, int $x1, int $y1, int $x2, int $y2, int $color, int $thickness = 1): void
    {
        imagesetthickness($img, max(1, $thickness));
        imagerectangle($img, $x1, $y1, $x2, $y2, $color);
        imagesetthickness($img, 1);
    }

    /** @param  \GdImage  $img */
    protected function filledCircle($img, int $cx, int $cy, int $r, int $color): void
    {
        imagefilledellipse($img, $cx, $cy, $r * 2, $r * 2, $color);
    }

    /** @param  \GdImage  $img */
    protected function text($img, int $size, int $x, int $y, string $text, int $color): void
    {
        if ($text === '') {
            return;
        }

        $font = $this->fontPath();
        if ($font && function_exists('imagettftext')) {
            imagettftext($img, $size, 0, $x, $y + $size, $color, $font, $text);

            return;
        }

        $builtin = max(1, min(5, (int) round($size / 8)));
        imagestring($img, $builtin, $x, $y, $this->ascii($text), $color);
    }

    /** @param  \GdImage  $img */
    protected function centered($img, int $size, int $y, string $text, int $color): void
    {
        $this->centeredInBox($img, $size, 0, self::WIDTH, $y, $text, $color);
    }

    /** @param  \GdImage  $img */
    protected function centeredInBox($img, int $size, int $boxX, int $boxW, int $y, string $text, int $color): void
    {
        if ($text === '') {
            return;
        }

        $font = $this->fontPath();
        if ($font && function_exists('imagettfbbox')) {
            $box = imagettfbbox($size, 0, $font, $text);
            $width = abs(($box[2] ?? 0) - ($box[0] ?? 0));
            $x = $boxX + (int) (($boxW - $width) / 2);
            $this->text($img, $size, $x, $y, $text, $color);

            return;
        }

        $this->text($img, $size, $boxX + 20, $y, $text, $color);
    }

    protected function fontPath(): ?string
    {
        foreach ([
            base_path('vendor/endroid/qr-code/assets/open_sans.ttf'),
            public_path('fonts/OpenSans-Regular.ttf'),
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function ascii(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted === false || $converted === '') {
            return preg_replace('/[^\x20-\x7E]/', '', $text) ?: ' ';
        }

        return $converted;
    }
}
