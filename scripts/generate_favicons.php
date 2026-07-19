<?php

/**
 * Generates PNG/ICO favicons from the brand calculator mark (GD).
 */

$public = dirname(__DIR__).DIRECTORY_SEPARATOR.'public';

function drawFavicon(int $size): GdImage
{
    $img = imagecreatetruecolor($size, $size);
    imagesavealpha($img, true);
    $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $transparent);

    $brand = imagecolorallocate($img, 11, 110, 79);
    $brandDark = imagecolorallocate($img, 8, 76, 55);
    $surface = imagecolorallocate($img, 247, 249, 248);
    $ink = imagecolorallocate($img, 26, 26, 26);
    $accent = imagecolorallocate($img, 244, 162, 89);

    $scale = $size / 64;

    // Rounded square background
    $radius = (int) round(14 * $scale);
    imagefilledrectangle($img, $radius, 0, $size - $radius - 1, $size - 1, $brand);
    imagefilledrectangle($img, 0, $radius, $size - 1, $size - $radius - 1, $brand);
    imagefilledellipse($img, $radius, $radius, $radius * 2, $radius * 2, $brand);
    imagefilledellipse($img, $size - $radius - 1, $radius, $radius * 2, $radius * 2, $brand);
    imagefilledellipse($img, $radius, $size - $radius - 1, $radius * 2, $radius * 2, $brandDark);
    imagefilledellipse($img, $size - $radius - 1, $size - $radius - 1, $radius * 2, $radius * 2, $brandDark);

    // Calculator body
    $x = (int) round(16 * $scale);
    $y = (int) round(12 * $scale);
    $w = (int) round(32 * $scale);
    $h = (int) round(40 * $scale);
    $br = max(2, (int) round(6 * $scale));
    imagefilledrectangle($img, $x + $br, $y, $x + $w - $br, $y + $h, $surface);
    imagefilledrectangle($img, $x, $y + $br, $x + $w, $y + $h - $br, $surface);
    imagefilledellipse($img, $x + $br, $y + $br, $br * 2, $br * 2, $surface);
    imagefilledellipse($img, $x + $w - $br, $y + $br, $br * 2, $br * 2, $surface);
    imagefilledellipse($img, $x + $br, $y + $h - $br, $br * 2, $br * 2, $surface);
    imagefilledellipse($img, $x + $w - $br, $y + $h - $br, $br * 2, $br * 2, $surface);

    // Display
    $dx = (int) round(20 * $scale);
    $dy = (int) round(16 * $scale);
    $dw = (int) round(24 * $scale);
    $dh = max(3, (int) round(10 * $scale));
    imagefilledrectangle($img, $dx, $dy, $dx + $dw, $dy + $dh, $ink);

    // Buttons
    $r = max(1, (int) round(2.4 * $scale));
    foreach ([[24, 34], [32, 34], [40, 34], [24, 42], [32, 42]] as [$cx, $cy]) {
        imagefilledellipse(
            $img,
            (int) round($cx * $scale),
            (int) round($cy * $scale),
            $r * 2,
            $r * 2,
            $brand
        );
    }

    // Accent key
    $ax = (int) round(37.5 * $scale);
    $ay = (int) round(39.5 * $scale);
    $aw = max(2, (int) round(5 * $scale));
    $ah = max(3, (int) round(8 * $scale));
    imagefilledrectangle($img, $ax, $ay, $ax + $aw, $ay + $ah, $accent);

    return $img;
}

function writePng(GdImage $img, string $path): void
{
    imagepng($img, $path);
    imagedestroy($img);
    echo "Wrote {$path}\n";
}

function writeIco(string $png16, string $png32, string $icoPath): void
{
    // Minimal ICO containing 16x16 and 32x32 PNG images (Vista+ style).
    $images = [];
    foreach ([$png16, $png32] as $png) {
        $data = file_get_contents($png);
        $info = getimagesize($png);
        $images[] = [
            'width' => $info[0] >= 256 ? 0 : $info[0],
            'height' => $info[1] >= 256 ? 0 : $info[1],
            'data' => $data,
        ];
    }

    $count = count($images);
    $offset = 6 + (16 * $count);
    $directory = '';
    $payload = '';

    foreach ($images as $image) {
        $size = strlen($image['data']);
        $directory .= pack('CCCCvvVV',
            $image['width'],
            $image['height'],
            0,
            0,
            1,
            32,
            $size,
            $offset
        );
        $payload .= $image['data'];
        $offset += $size;
    }

    $ico = pack('vvv', 0, 1, $count).$directory.$payload;
    file_put_contents($icoPath, $ico);
    echo "Wrote {$icoPath}\n";
}

$png16 = $public.DIRECTORY_SEPARATOR.'favicon-16x16.png';
$png32 = $public.DIRECTORY_SEPARATOR.'favicon-32x32.png';
$png180 = $public.DIRECTORY_SEPARATOR.'apple-touch-icon.png';
$ico = $public.DIRECTORY_SEPARATOR.'favicon.ico';

writePng(drawFavicon(16), $png16);
writePng(drawFavicon(32), $png32);
writePng(drawFavicon(180), $png180);
writeIco($png16, $png32, $ico);

echo "Favicons generated.\n";
