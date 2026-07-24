<?php

namespace App\Services\Qr;

use App\Enums\Qr\QrEyeStyle;
use App\Enums\Qr\QrFrameStyle;
use App\Enums\Qr\QrModuleStyle;
use InvalidArgumentException;

class QrImageStyleProcessor
{
    public function applyRasterStyles(
        string $pngBinary,
        QrModuleStyle $moduleStyle,
        QrEyeStyle $eyeStyle,
        QrFrameStyle $frameStyle,
        string $frameLabel,
        string $foreground,
        string $background,
    ): string {
        $src = @imagecreatefromstring($pngBinary);
        if ($src === false) {
            throw new InvalidArgumentException('Unable to process QR image.');
        }

        imagesavealpha($src, true);

        if ($moduleStyle !== QrModuleStyle::Square || $eyeStyle !== QrEyeStyle::Square) {
            $ratio = match ($moduleStyle) {
                QrModuleStyle::Dots => 0.12,
                QrModuleStyle::Rounded => 0.08,
                default => 0.05,
            };
            if ($eyeStyle === QrEyeStyle::Leaf) {
                $ratio = max($ratio, 0.1);
            }
            $src = $this->roundCorners($src, (int) max(8, imagesx($src) * $ratio));
        }

        if ($frameStyle !== QrFrameStyle::None) {
            $src = $this->applyFrame($src, $frameStyle, $frameLabel, $foreground, $background);
        }

        ob_start();
        imagepng($src);
        $out = (string) ob_get_clean();
        imagedestroy($src);

        return $out;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    protected function roundCorners($image, int $radius)
    {
        $w = imagesx($image);
        $h = imagesy($image);
        $radius = max(1, min($radius, (int) floor(min($w, $h) / 2)));

        $out = imagecreatetruecolor($w, $h);
        imagesavealpha($out, true);
        $clear = imagecolorallocatealpha($out, 0, 0, 0, 127);
        imagefill($out, 0, 0, $clear);
        imagecopy($out, $image, 0, 0, 0, 0, $w, $h);

        $corners = [
            [0, 0, $radius, $radius, 180, 270],
            [$w - $radius, 0, $w, $radius, 270, 360],
            [0, $h - $radius, $radius, $h, 90, 180],
            [$w - $radius, $h - $radius, $w, $h, 0, 90],
        ];

        foreach ($corners as [$x1, $y1, $x2, $y2]) {
            for ($x = $x1; $x < $x2; $x++) {
                for ($y = $y1; $y < $y2; $y++) {
                    $cx = $x1 < $w / 2 ? $radius : $w - $radius;
                    $cy = $y1 < $h / 2 ? $radius : $h - $radius;
                    $dx = $x - $cx + 0.5;
                    $dy = $y - $cy + 0.5;
                    if (($dx * $dx) + ($dy * $dy) > ($radius * $radius)) {
                        imagesetpixel($out, $x, $y, $clear);
                    }
                }
            }
        }

        imagedestroy($image);

        return $out;
    }

    /**
     * @param  \GdImage  $qr
     * @return \GdImage
     */
    protected function applyFrame($qr, QrFrameStyle $frameStyle, string $label, string $foreground, string $background)
    {
        $qw = imagesx($qr);
        $qh = imagesy($qr);
        $pad = (int) max(16, $qw * 0.08);
        $banner = ($frameStyle === QrFrameStyle::Banner || $frameStyle === QrFrameStyle::Card) ? (int) max(36, $qw * 0.14) : 0;
        $border = $frameStyle === QrFrameStyle::Simple ? 8 : ($frameStyle === QrFrameStyle::Card ? 14 : 6);

        $tw = $qw + ($pad * 2) + ($border * 2);
        $th = $qh + ($pad * 2) + ($border * 2) + $banner;

        $canvas = imagecreatetruecolor($tw, $th);
        imagesavealpha($canvas, true);
        [$br, $bg, $bb] = $this->hexToRgb($background);
        [$fr, $fg, $fb] = $this->hexToRgb($foreground);
        $bgColor = imagecolorallocate($canvas, $br, $bg, $bb);
        $fgColor = imagecolorallocate($canvas, $fr, $fg, $fb);
        imagefill($canvas, 0, 0, $bgColor);

        if ($frameStyle === QrFrameStyle::Simple || $frameStyle === QrFrameStyle::Card) {
            imagefilledrectangle($canvas, 0, 0, $tw - 1, $th - 1, $fgColor);
            imagefilledrectangle($canvas, $border, $border, $tw - $border - 1, $th - $border - 1, $bgColor);
        }

        $dstX = $border + $pad;
        $dstY = $border + $pad;
        imagecopy($canvas, $qr, $dstX, $dstY, 0, 0, $qw, $qh);

        if ($banner > 0) {
            $text = $label !== '' ? mb_substr($label, 0, 40) : 'Scan me';
            $textY = $dstY + $qh + (int) ($banner / 2) - 6;
            $font = 5;
            $textWidth = imagefontwidth($font) * strlen($text);
            $textX = (int) max($dstX, ($tw - $textWidth) / 2);
            imagestring($canvas, $font, $textX, $textY, $text, $fgColor);
        }

        imagedestroy($qr);

        return $canvas;
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    public function pngToJpg(string $pngBinary, int $quality = 90): string
    {
        $img = @imagecreatefromstring($pngBinary);
        if ($img === false) {
            throw new InvalidArgumentException('Unable to convert QR to JPG.');
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $canvas = imagecreatetruecolor($w, $h);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopy($canvas, $img, 0, 0, 0, 0, $w, $h);
        ob_start();
        imagejpeg($canvas, null, $quality);
        $out = (string) ob_get_clean();
        imagedestroy($img);
        imagedestroy($canvas);

        return $out;
    }
}
