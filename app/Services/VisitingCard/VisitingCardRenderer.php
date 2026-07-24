<?php

namespace App\Services\VisitingCard;

use App\DTOs\VisitingCard\VisitingCardData;
use App\Enums\Qr\QrErrorCorrection;
use App\Enums\Qr\QrOutputFormat;
use App\Enums\VisitingCard\CardTemplate;
use App\Services\Qr\QrGeneratorService;
use InvalidArgumentException;

/**
 * Renders print-ready visiting cards (1050×600 ≈ 3.5×2 in @ 300dpi).
 * 32 designer layouts across Professional, Minimal, Bold, Premium, Creative & Corporate.
 */
class VisitingCardRenderer
{
    public const WIDTH = 1050;

    public const HEIGHT = 600;

    public function __construct(protected QrGeneratorService $qr)
    {
    }

    public function renderPng(VisitingCardData $card): string
    {
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        if ($img === false) {
            throw new InvalidArgumentException('Unable to create card canvas.');
        }
        imagealphablending($img, true);
        imagesavealpha($img, true);

        [$br, $bg, $bb] = VisitingCardData::rgb($card->backgroundColor);
        imagefill($img, 0, 0, imagecolorallocate($img, $br, $bg, $bb));

        match ($card->template) {
            CardTemplate::Classic => $this->drawClassic($img, $card),
            CardTemplate::Modern => $this->drawModern($img, $card),
            CardTemplate::Minimal => $this->drawMinimal($img, $card),
            CardTemplate::Bold => $this->drawBold($img, $card),
            CardTemplate::Split => $this->drawSplit($img, $card),
            CardTemplate::Noir => $this->drawNoir($img, $card),
            CardTemplate::Editorial => $this->drawEditorial($img, $card),
            CardTemplate::Luxe => $this->drawLuxe($img, $card),
            CardTemplate::Horizon => $this->drawHorizon($img, $card),
            CardTemplate::Diagonal => $this->drawDiagonal($img, $card),
            CardTemplate::Duotone => $this->drawDuotone($img, $card),
            CardTemplate::Corner => $this->drawCorner($img, $card),
            CardTemplate::Monogram => $this->drawMonogram($img, $card),
            CardTemplate::Stack => $this->drawStack($img, $card),
            CardTemplate::Ribbon => $this->drawRibbon($img, $card),
            CardTemplate::Geometric => $this->drawGeometric($img, $card),
            CardTemplate::Neon => $this->drawNeon($img, $card),
            CardTemplate::Swiss => $this->drawSwiss($img, $card),
            CardTemplate::Crest => $this->drawCrest($img, $card),
            CardTemplate::Cascade => $this->drawCascade($img, $card),
            CardTemplate::Aurora => $this->drawAurora($img, $card),
            CardTemplate::Marble => $this->drawMarble($img, $card),
            CardTemplate::Orbit => $this->drawOrbit($img, $card),
            CardTemplate::Pulse => $this->drawPulse($img, $card),
            CardTemplate::Ledger => $this->drawLedger($img, $card),
            CardTemplate::Studio => $this->drawStudio($img, $card),
            CardTemplate::Ink => $this->drawInk($img, $card),
            CardTemplate::Metro => $this->drawMetro($img, $card),
            CardTemplate::Prism => $this->drawPrism($img, $card),
            CardTemplate::Velvet => $this->drawVelvet($img, $card),
            CardTemplate::Executive => $this->drawExecutive($img, $card),
            CardTemplate::Wave => $this->drawWave($img, $card),
        };

        if ($card->includeQr) {
            $this->drawQr($img, $card);
        }

        if ($card->logoPath && is_file($card->logoPath)) {
            $this->drawLogo($img, $card);
        }

        ob_start();
        imagepng($img);
        $binary = (string) ob_get_clean();
        imagedestroy($img);

        return $binary;
    }

    /** @param  \GdImage  $img */
    protected function drawClassic($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, 88, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 88, 0, 96, self::HEIGHT, $c['secondary']);

        $this->text($img, 36, 140, 95, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 140, 160, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 140, 280, $c['muted']);
        if ($card->tagline !== '') {
            $this->text($img, 14, 140, 520, $card->tagline, $c['primary']);
        }
    }

    /** @param  \GdImage  $img */
    protected function drawModern($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, 168, $c['primary']);
        imagefilledrectangle($img, 0, 168, self::WIDTH, 180, $c['secondary']);

        $this->text($img, 34, 56, 52, $card->displayName(), $c['white']);
        if ($card->jobTitle !== '') {
            $this->text($img, 17, 56, 108, $card->jobTitle, $c['white']);
        }
        $y = 220;
        if ($card->company !== '') {
            $this->text($img, 22, 56, $y, $card->company, $c['text']);
            $y += 42;
        }
        $this->contacts($img, $card, 56, $y, $c['muted']);
    }

    /** @param  \GdImage  $img */
    protected function drawMinimal($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 56, 64, 210, 72, $c['primary']);
        $this->text($img, 38, 56, 110, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 15, 56, 175, strtoupper($card->jobTitle), $c['primary']);
        }
        if ($card->company !== '') {
            $this->text($img, 17, 56, 215, $card->company, $c['muted']);
        }
        $this->contacts($img, $card, 56, 300, $c['muted']);
    }

    /** @param  \GdImage  $img */
    protected function drawBold($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 42, 42, self::WIDTH - 42, self::HEIGHT - 42, $c['bg']);
        imagefilledrectangle($img, 42, 42, self::WIDTH - 42, 52, $c['secondary']);

        $this->text($img, 36, 80, 100, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 80, 165, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 80, 290, $c['muted']);
    }

    /** @param  \GdImage  $img */
    protected function drawSplit($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, 360, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 0, self::HEIGHT - 88, 360, self::HEIGHT, $c['secondary']);

        $this->text($img, 22, 40, 70, $card->company !== '' ? $card->company : 'Company', $c['white']);
        if ($card->tagline !== '') {
            $this->text($img, 13, 40, 120, $card->tagline, $c['white']);
        }

        $this->text($img, 34, 420, 100, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 17, 420, 160, $card->jobTitle, $c['primary']);
        }
        $this->contacts($img, $card, 420, 240, $c['muted']);
    }

    /** Dark-mode luxury — 2026 trend. */
    /** @param  \GdImage  $img */
    protected function drawNoir($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['bg']);
        imagefilledrectangle($img, 70, 70, 74, 220, $c['primary']);
        imagefilledrectangle($img, 70, self::HEIGHT - 70, self::WIDTH - 70, self::HEIGHT - 66, $c['primary']);

        $this->text($img, 40, 100, 90, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 100, 155, strtoupper($card->jobTitle), $c['primary']);
        }
        if ($card->company !== '') {
            $this->text($img, 18, 100, 200, $card->company, $c['mutedLight']);
        }
        $this->contacts($img, $card, 100, 300, $c['mutedLight']);
    }

    /** Oversized typography-first. */
    /** @param  \GdImage  $img */
    protected function drawEditorial($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $name = $card->displayName();
        $this->text($img, 52, 48, 70, $this->firstWord($name), $c['text']);
        $rest = trim(substr($name, strlen($this->firstWord($name))));
        if ($rest !== '') {
            $this->text($img, 52, 48, 145, $rest, $c['text']);
        }
        imagefilledrectangle($img, 48, 230, 160, 238, $c['secondary']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 48, 260, strtoupper($card->jobTitle), $c['secondary']);
        }
        if ($card->company !== '') {
            $this->text($img, 18, 48, 300, $card->company, $c['muted']);
        }
        $this->contacts($img, $card, 48, 370, $c['muted'], 30);
    }

    /** Foil-inspired thin gold rules. */
    /** @param  \GdImage  $img */
    protected function drawLuxe($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $this->strokeRect($img, 36, 36, self::WIDTH - 36, self::HEIGHT - 36, $c['primary'], 3);
        $this->strokeRect($img, 48, 48, self::WIDTH - 48, self::HEIGHT - 48, $c['secondary'], 1);

        $this->centeredText($img, 34, 110, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->centeredText($img, 15, 170, strtoupper($card->jobTitle), $c['primary']);
        }
        imagefilledrectangle($img, 470, 210, 580, 214, $c['primary']);
        if ($card->company !== '') {
            $this->centeredText($img, 18, 240, $card->company, $c['muted']);
        }
        $this->centeredContacts($img, $card, 310, $c['muted']);
    }

    /** Bottom accent horizon strip. */
    /** @param  \GdImage  $img */
    protected function drawHorizon($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, self::HEIGHT - 110, self::WIDTH, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 0, self::HEIGHT - 118, self::WIDTH, self::HEIGHT - 110, $c['secondary']);

        $this->text($img, 36, 56, 80, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 56, 145, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 56, 250, $c['muted'], 32);
        if ($card->tagline !== '') {
            $this->text($img, 15, 56, self::HEIGHT - 70, $card->tagline, $c['white']);
        } elseif ($card->website !== '') {
            $this->text($img, 15, 56, self::HEIGHT - 70, preg_replace('#^https?://#i', '', $card->website) ?: '', $c['white']);
        }
    }

    /** Diagonal color cut. */
    /** @param  \GdImage  $img */
    protected function drawDiagonal($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledpolygon($img, [0, 0, 520, 0, 280, self::HEIGHT, 0, self::HEIGHT], $c['primary']);
        imagefilledpolygon($img, [520, 0, 580, 0, 340, self::HEIGHT, 280, self::HEIGHT], $c['secondary']);

        $this->text($img, 20, 40, 80, $card->company !== '' ? $card->company : '', $c['white']);
        if ($card->tagline !== '') {
            $this->text($img, 13, 40, 125, $card->tagline, $c['white']);
        }

        $this->text($img, 34, 560, 120, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 560, 180, $card->jobTitle, $c['primary']);
        }
        $this->contacts($img, $card, 560, 250, $c['muted']);
    }

    /** 50/50 brand + details. */
    /** @param  \GdImage  $img */
    protected function drawDuotone($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, (int) (self::WIDTH / 2), self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, (int) (self::WIDTH / 2), 0, self::WIDTH, self::HEIGHT, $c['secondary']);

        $this->text($img, 28, 48, 180, $card->company !== '' ? $card->company : 'Brand', $c['white']);
        if ($card->tagline !== '') {
            $this->text($img, 14, 48, 240, $card->tagline, $c['white']);
        }

        $x = (int) (self::WIDTH / 2) + 48;
        $this->text($img, 30, $x, 140, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 15, $x, 195, $card->jobTitle, $c['primary']);
        }
        $this->contacts($img, $card, $x, 270, $c['muted']);
    }

    /** Geometric corner accents. */
    /** @param  \GdImage  $img */
    protected function drawCorner($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledpolygon($img, [0, 0, 180, 0, 0, 180], $c['primary']);
        imagefilledpolygon($img, [self::WIDTH, self::HEIGHT, self::WIDTH - 160, self::HEIGHT, self::WIDTH, self::HEIGHT - 160], $c['secondary']);

        $this->text($img, 36, 70, 200, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 70, 265, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 70, 380, $c['muted'], 32);
    }

    /** Large monogram badge + details. */
    /** @param  \GdImage  $img */
    protected function drawMonogram($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $cx = 150;
        $cy = 300;
        imagefilledellipse($img, $cx, $cy, 180, 180, $c['primary']);
        imageellipse($img, $cx, $cy, 200, 200, $c['secondary']);
        $initial = strtoupper(substr($this->firstWord($card->displayName()), 0, 1) ?: 'A');
        $this->text($img, 56, $cx - 22, $cy - 40, $initial, $c['white']);

        $this->text($img, 34, 320, 140, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 320, 205, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 320, 320, $c['muted']);
    }

    /** Centered calm stack. */
    /** @param  \GdImage  $img */
    protected function drawStack($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        if ($card->company !== '') {
            $this->centeredText($img, 14, 90, strtoupper($card->company), $c['primary']);
        }
        $this->centeredText($img, 40, 170, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->centeredText($img, 16, 235, $card->jobTitle, $c['muted']);
        }
        imagefilledrectangle($img, 480, 280, 570, 284, $c['secondary']);
        $this->centeredContacts($img, $card, 330, $c['muted']);
    }

    /** Vertical ribbon / bookmark. */
    /** @param  \GdImage  $img */
    protected function drawRibbon($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, 120, self::HEIGHT, $c['primary']);
        imagefilledpolygon($img, [0, self::HEIGHT - 80, 60, self::HEIGHT - 40, 120, self::HEIGHT - 80, 120, self::HEIGHT, 0, self::HEIGHT], $c['secondary']);

        $this->text($img, 34, 170, 100, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 170, 165, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 170, 290, $c['muted']);
        if ($card->tagline !== '') {
            $this->text($img, 14, 170, 520, $card->tagline, $c['secondary']);
        }
    }

    /** Dark teal geometric mesh. */
    /** @param  \GdImage  $img */
    protected function drawGeometric($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['bg']);
        for ($i = 0; $i < 8; $i++) {
            $x = 40 + $i * 130;
            imagefilledrectangle($img, $x, 0, $x + 40, self::HEIGHT, $c['secondary']);
        }
        imagefilledrectangle($img, 60, 70, self::WIDTH - 60, self::HEIGHT - 70, $c['primary']);

        $this->text($img, 34, 100, 140, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 100, 210, $c['white'], $c['mutedLight']);
        $this->contacts($img, $card, 100, 340, $c['mutedLight']);
    }

    /** Tech neon on dark. */
    /** @param  \GdImage  $img */
    protected function drawNeon($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['bg']);
        imagefilledrectangle($img, 0, 0, self::WIDTH, 8, $c['primary']);
        imagefilledrectangle($img, 0, self::HEIGHT - 8, self::WIDTH, self::HEIGHT, $c['secondary']);

        $this->text($img, 38, 60, 100, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 60, 165, strtoupper($card->jobTitle), $c['primary']);
        }
        if ($card->company !== '') {
            $this->text($img, 18, 60, 210, $card->company, $c['secondary']);
        }
        $this->contacts($img, $card, 60, 300, $c['mutedLight']);
    }

    /** Swiss International Style grid. */
    /** @param  \GdImage  $img */
    protected function drawSwiss($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, 220, 220, $c['primary']);
        imagefilledrectangle($img, self::WIDTH - 16, 0, self::WIDTH, self::HEIGHT, $c['secondary']);

        $this->text($img, 14, 40, 250, strtoupper($card->company !== '' ? $card->company : 'STUDIO'), $c['text']);
        $this->text($img, 36, 40, 300, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 40, 365, $card->jobTitle, $c['muted']);
        }
        $this->contacts($img, $card, 520, 250, $c['muted'], 34);
    }

    /** Formal crest / centered seal. */
    /** @param  \GdImage  $img */
    protected function drawCrest($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $this->strokeRect($img, 28, 28, self::WIDTH - 28, self::HEIGHT - 28, $c['primary'], 2);
        imagefilledellipse($img, (int) (self::WIDTH / 2), 120, 70, 70, $c['secondary']);
        imageellipse($img, (int) (self::WIDTH / 2), 120, 90, 90, $c['primary']);

        $this->centeredText($img, 32, 200, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->centeredText($img, 14, 255, strtoupper($card->jobTitle), $c['secondary']);
        }
        if ($card->company !== '') {
            $this->centeredText($img, 17, 300, $card->company, $c['muted']);
        }
        $this->centeredContacts($img, $card, 360, $c['muted']);
    }

    /** Layered color cascade bands. */
    /** @param  \GdImage  $img */
    protected function drawCascade($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 0, 140, self::WIDTH, 280, $c['secondary']);
        [$pr, $pg, $pb] = VisitingCardData::rgb($card->primaryColor);
        $deep = imagecolorallocate($img, max(0, $pr - 30), max(0, $pg - 30), max(0, $pb - 30));
        imagefilledrectangle($img, 0, 280, self::WIDTH, self::HEIGHT, $deep);

        $this->text($img, 34, 60, 50, $card->displayName(), $c['white']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 60, 105, $card->jobTitle, $c['white']);
        }
        if ($card->company !== '') {
            $this->text($img, 22, 60, 180, $card->company, $c['white']);
        }
        $this->contacts($img, $card, 60, 340, $c['white']);
    }

    /** Soft stacked aurora bands on a deep canvas. */
    /** @param  \GdImage  $img */
    protected function drawAurora($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['bg']);
        [$pr, $pg, $pb] = VisitingCardData::rgb($card->primaryColor);
        [$sr, $sg, $sb] = VisitingCardData::rgb($card->secondaryColor);
        $band1 = imagecolorallocate($img, $pr, $pg, $pb);
        $band2 = imagecolorallocate($img, $sr, $sg, $sb);
        $band3 = imagecolorallocate($img, min(255, $pr + 40), min(255, $pg + 20), min(255, $pb + 50));
        imagefilledellipse($img, 180, -40, 720, 420, $band1);
        imagefilledellipse($img, 780, 120, 640, 380, $band2);
        imagefilledellipse($img, 420, 520, 700, 360, $band3);
        imagefilledrectangle($img, 0, 0, 12, self::HEIGHT, $c['secondary']);

        $this->text($img, 34, 56, 160, $card->displayName(), $c['white']);
        $this->roleCompany($img, $card, 56, 220, $c['secondary'], $c['mutedLight']);
        $this->contacts($img, $card, 56, 320, $c['mutedLight']);
    }

    /** Cream marble with gold double-rule frame. */
    /** @param  \GdImage  $img */
    protected function drawMarble($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $this->strokeRect($img, 28, 28, self::WIDTH - 28, self::HEIGHT - 28, $c['primary'], 2);
        $this->strokeRect($img, 38, 38, self::WIDTH - 38, self::HEIGHT - 38, $c['secondary'], 1);
        imagefilledrectangle($img, 70, 95, 260, 99, $c['primary']);

        $this->text($img, 34, 70, 130, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 70, 190, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 70, 300, $c['muted']);
        if ($card->tagline !== '') {
            $this->text($img, 13, 70, 520, $card->tagline, $c['secondary']);
        }
    }

    /** Centered orbit ring with monogram-style focus. */
    /** @param  \GdImage  $img */
    protected function drawOrbit($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledellipse($img, 160, 300, 280, 280, $c['primary']);
        imagefilledellipse($img, 160, 300, 220, 220, $c['bg']);
        imagefilledellipse($img, 160, 300, 28, 28, $c['secondary']);

        $initial = mb_strtoupper(mb_substr($card->displayName(), 0, 1));
        $this->text($img, 42, 140, 278, $initial, $c['primary']);

        $this->text($img, 32, 360, 160, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 360, 220, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 360, 320, $c['muted']);
    }

    /** Vertical pulse accent bars + clean body. */
    /** @param  \GdImage  $img */
    protected function drawPulse($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $bars = [48, 72, 96, 120, 144];
        $heights = [220, 340, 180, 400, 260];
        foreach ($bars as $i => $x) {
            $h = $heights[$i];
            $y = (int) ((self::HEIGHT - $h) / 2);
            $col = $i % 2 === 0 ? $c['primary'] : $c['secondary'];
            imagefilledrectangle($img, $x, $y, $x + 14, $y + $h, $col);
        }

        $this->text($img, 34, 220, 140, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 220, 200, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 220, 300, $c['muted']);
    }

    /** Corporate ledger with hairline rules. */
    /** @param  \GdImage  $img */
    protected function drawLedger($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, 8, $c['primary']);
        imagefilledrectangle($img, 56, 120, self::WIDTH - 56, 122, $c['secondary']);
        imagefilledrectangle($img, 56, 250, self::WIDTH - 200, 251, $c['secondary']);

        if ($card->company !== '') {
            $this->text($img, 14, 56, 48, strtoupper($card->company), $c['primary']);
        }
        $this->text($img, 36, 56, 150, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->text($img, 16, 56, 210, $card->jobTitle, $c['muted']);
        }
        $this->contacts($img, $card, 56, 290, $c['muted']);
    }

    /** Creative studio: solid left panel. */
    /** @param  \GdImage  $img */
    protected function drawStudio($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, 320, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 320, 0, 332, self::HEIGHT, $c['secondary']);

        if ($card->company !== '') {
            $this->text($img, 16, 40, 80, strtoupper($card->company), $c['white']);
        }
        if ($card->tagline !== '') {
            $this->text($img, 13, 40, 500, $card->tagline, $c['white']);
        }

        $this->text($img, 34, 380, 150, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 380, 210, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 380, 310, $c['muted']);
    }

    /** Oversized ink initial watermark. */
    /** @param  \GdImage  $img */
    protected function drawInk($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $initial = mb_strtoupper(mb_substr($card->displayName(), 0, 1));
        [$tr, $tg, $tb] = VisitingCardData::rgb($card->textColor);
        $wash = imagecolorallocatealpha($img, $tr, $tg, $tb, 100);
        $this->text($img, 220, 40, 80, $initial, $wash);
        imagefilledrectangle($img, 56, 56, 160, 62, $c['primary']);

        $this->text($img, 34, 56, 160, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 56, 220, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 56, 320, $c['muted']);
    }

    /** Clean card with bold metro bottom bar. */
    /** @param  \GdImage  $img */
    protected function drawMetro($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, self::HEIGHT - 90, self::WIDTH, self::HEIGHT, $c['primary']);
        imagefilledrectangle($img, 0, self::HEIGHT - 98, self::WIDTH, self::HEIGHT - 90, $c['secondary']);

        $this->text($img, 36, 56, 110, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 56, 175, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 56, 280, $c['muted'], 30);

        $footer = trim(($card->company !== '' ? $card->company.'  ·  ' : '').($card->tagline !== '' ? $card->tagline : ''));
        if ($footer !== '') {
            $this->text($img, 14, 56, self::HEIGHT - 52, $footer, $c['white']);
        }
    }

    /** Prism corner triangles. */
    /** @param  \GdImage  $img */
    protected function drawPrism($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledpolygon($img, [0, 0, 340, 0, 0, 340], $c['primary']);
        imagefilledpolygon($img, [self::WIDTH, self::HEIGHT, self::WIDTH - 280, self::HEIGHT, self::WIDTH, self::HEIGHT - 280], $c['secondary']);
        imagefilledpolygon($img, [self::WIDTH, 0, self::WIDTH - 120, 0, self::WIDTH, 120], $c['primary']);

        $this->text($img, 34, 80, 200, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 80, 265, $c['primary'], $c['muted']);
        $this->contacts($img, $card, 80, 360, $c['muted']);
    }

    /** Deep velvet luxury night. */
    /** @param  \GdImage  $img */
    protected function drawVelvet($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 48, 48, 56, self::HEIGHT - 48, $c['primary']);
        imagefilledrectangle($img, 64, 48, 68, self::HEIGHT - 48, $c['secondary']);

        $this->text($img, 34, 110, 140, $card->displayName(), $c['text']);
        $this->roleCompany($img, $card, 110, 205, $c['primary'], $c['mutedLight']);
        $this->contacts($img, $card, 110, 300, $c['mutedLight']);
        if ($card->tagline !== '') {
            $this->text($img, 13, 110, 520, $card->tagline, $c['secondary']);
        }
    }

    /** Formal double-border executive. */
    /** @param  \GdImage  $img */
    protected function drawExecutive($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        $this->strokeRect($img, 24, 24, self::WIDTH - 24, self::HEIGHT - 24, $c['primary'], 3);
        $this->strokeRect($img, 36, 36, self::WIDTH - 36, self::HEIGHT - 36, $c['secondary'], 1);
        imagefilledrectangle($img, (int) (self::WIDTH / 2) - 40, 70, (int) (self::WIDTH / 2) + 40, 74, $c['secondary']);

        $this->centeredText($img, 32, 130, $card->displayName(), $c['text']);
        if ($card->jobTitle !== '') {
            $this->centeredText($img, 15, 190, strtoupper($card->jobTitle), $c['primary']);
        }
        if ($card->company !== '') {
            $this->centeredText($img, 17, 240, $card->company, $c['muted']);
        }
        $this->centeredContacts($img, $card, 310, $c['muted']);
    }

    /** Ocean wave bottom edge. */
    /** @param  \GdImage  $img */
    protected function drawWave($img, VisitingCardData $card): void
    {
        $c = $this->palette($img, $card);
        imagefilledrectangle($img, 0, 0, self::WIDTH, self::HEIGHT, $c['bg']);
        [$pr, $pg, $pb] = VisitingCardData::rgb($card->primaryColor);
        [$sr, $sg, $sb] = VisitingCardData::rgb($card->secondaryColor);
        $deep = imagecolorallocate($img, max(0, $pr - 25), max(0, $pg - 25), max(0, $pb - 25));

        // Approximate wave with stacked polygons
        imagefilledpolygon($img, [
            0, 340, 180, 300, 360, 360, 540, 290, 720, 350, 900, 310, 1050, 340,
            1050, 600, 0, 600,
        ], $c['secondary']);
        imagefilledpolygon($img, [
            0, 380, 160, 350, 340, 400, 520, 340, 700, 390, 880, 355, 1050, 385,
            1050, 600, 0, 600,
        ], $c['primary']);
        imagefilledpolygon($img, [
            0, 440, 200, 410, 400, 460, 600, 415, 800, 455, 1050, 430,
            1050, 600, 0, 600,
        ], $deep);

        $this->text($img, 34, 56, 100, $card->displayName(), $c['white']);
        $this->roleCompany($img, $card, 56, 165, $c['secondary'], $c['mutedLight']);
        $this->contacts($img, $card, 56, 250, $c['mutedLight'], 30);
    }

    /**
     * @param  \GdImage  $img
     * @return array<string, int>
     */
    protected function palette($img, VisitingCardData $card): array
    {
        [$pr, $pg, $pb] = VisitingCardData::rgb($card->primaryColor);
        [$sr, $sg, $sb] = VisitingCardData::rgb($card->secondaryColor);
        [$tr, $tg, $tb] = VisitingCardData::rgb($card->textColor);
        [$br, $bg, $bb] = VisitingCardData::rgb($card->backgroundColor);

        return [
            'primary' => imagecolorallocate($img, $pr, $pg, $pb),
            'secondary' => imagecolorallocate($img, $sr, $sg, $sb),
            'text' => imagecolorallocate($img, $tr, $tg, $tb),
            'bg' => imagecolorallocate($img, $br, $bg, $bb),
            'white' => imagecolorallocate($img, 255, 255, 255),
            'muted' => imagecolorallocate($img, 90, 90, 90),
            'mutedLight' => imagecolorallocate($img, 180, 180, 180),
        ];
    }

    /** @param  \GdImage  $img */
    protected function roleCompany($img, VisitingCardData $card, int $x, int $y, int $roleColor, int $companyColor): void
    {
        if ($card->jobTitle !== '') {
            $this->text($img, 17, $x, $y, $card->jobTitle, $roleColor);
            $y += 40;
        }
        if ($card->company !== '') {
            $this->text($img, 17, $x, $y, $card->company, $companyColor);
        }
    }

    /** @param  \GdImage  $img */
    protected function contacts($img, VisitingCardData $card, int $x, int $y, int $color, int $gap = 34): void
    {
        foreach ($this->contactLines($card) as $line) {
            $this->text($img, 15, $x, $y, $line, $color);
            $y += $gap;
        }
    }

    /** @param  \GdImage  $img */
    protected function centeredContacts($img, VisitingCardData $card, int $y, int $color): void
    {
        foreach ($this->contactLines($card) as $line) {
            $this->centeredText($img, 15, $y, $line, $color);
            $y += 34;
        }
    }

    /**
     * @return list<string>
     */
    protected function contactLines(VisitingCardData $card): array
    {
        $lines = [];
        if ($card->phone !== '') {
            $lines[] = $card->phone;
        }
        if ($card->email !== '') {
            $lines[] = $card->email;
        }
        if ($card->website !== '') {
            $lines[] = preg_replace('#^https?://#i', '', $card->website) ?: $card->website;
        }
        if ($card->address !== '') {
            $lines[] = $card->address;
        }

        return $lines;
    }

    protected function firstWord(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return (string) ($parts[0] ?? $name);
    }

    /** @param  \GdImage  $img */
    protected function strokeRect($img, int $x1, int $y1, int $x2, int $y2, int $color, int $thickness = 2): void
    {
        imagesetthickness($img, $thickness);
        imagerectangle($img, $x1, $y1, $x2, $y2, $color);
        imagesetthickness($img, 1);
    }

    /** @param  \GdImage  $img */
    protected function centeredText($img, int $size, int $y, string $text, int $color): void
    {
        $font = $this->fontPath();
        if ($font && function_exists('imagettfbbox')) {
            $box = imagettfbbox($size, 0, $font, $text);
            $width = abs(($box[2] ?? 0) - ($box[0] ?? 0));
            $x = (int) ((self::WIDTH - $width) / 2);
            $this->text($img, $size, $x, $y, $text, $color);

            return;
        }
        $this->text($img, $size, 200, $y, $text, $color);
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
        imagestring($img, $builtin, $x, $y, $this->asciiFallback($text), $color);
    }

    protected function fontPath(): ?string
    {
        $candidates = [
            base_path('vendor/endroid/qr-code/assets/open_sans.ttf'),
            public_path('fonts/OpenSans-Regular.ttf'),
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function asciiFallback(string $text): string
    {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        if ($converted === false || $converted === '') {
            return preg_replace('/[^\x20-\x7E]/', '', $text) ?: ' ';
        }

        return $converted;
    }

    /** @param  \GdImage  $img */
    protected function drawQr($img, VisitingCardData $card): void
    {
        try {
            $anchor = $card->template->qrAnchor();
            $size = $anchor['size'];
            $result = $this->qr->generate([
                'type' => 'text',
                'input' => ['text' => $card->qrPayload()],
                'size' => max(160, $size + 20),
                'foreground' => '#000000',
                'background' => '#FFFFFF',
                'margin' => 4,
                'error_correction' => QrErrorCorrection::Medium->value,
                'module_style' => 'square',
                'eye_style' => 'square',
                'frame_style' => 'none',
                'save_history' => false,
            ], QrOutputFormat::Png, null, false);

            $qr = @imagecreatefromstring($result->binary);
            if ($qr === false) {
                return;
            }

            $dstX = match ($anchor['x']) {
                'left' => 40,
                'center' => (int) ((self::WIDTH - $size) / 2),
                default => self::WIDTH - $size - 40,
            };
            $dstY = match ($anchor['y']) {
                'top' => 40,
                'middle' => (int) ((self::HEIGHT - $size) / 2),
                default => self::HEIGHT - $size - 40,
            };

            imagecopyresampled($img, $qr, $dstX, $dstY, 0, 0, $size, $size, imagesx($qr), imagesy($qr));
            imagedestroy($qr);
        } catch (\Throwable) {
            // QR is optional
        }
    }

    /** @param  \GdImage  $img */
    protected function drawLogo($img, VisitingCardData $card): void
    {
        $logo = @imagecreatefromstring((string) file_get_contents((string) $card->logoPath));
        if ($logo === false) {
            return;
        }

        $anchor = $card->template->logoAnchor();
        $target = $anchor['size'];
        $lw = imagesx($logo);
        $lh = imagesy($logo);
        $scale = min($target / max(1, $lw), $target / max(1, $lh));
        $nw = (int) max(1, $lw * $scale);
        $nh = (int) max(1, $lh * $scale);
        $pad = 8;

        $x = match ($anchor['x']) {
            'left' => 40,
            'center' => (int) ((self::WIDTH - $nw) / 2),
            default => self::WIDTH - $nw - 48,
        };
        $y = match ($anchor['y']) {
            'middle' => (int) ((self::HEIGHT - $nh) / 2),
            'bottom' => self::HEIGHT - $nh - 48,
            default => 40,
        };

        // Template-specific fine-tuning
        if ($card->template === CardTemplate::Classic) {
            $x = 130;
            $y = 40;
        } elseif ($card->template === CardTemplate::Split) {
            $x = 40;
            $y = 180;
        } elseif ($card->template === CardTemplate::Ribbon) {
            $x = 18;
            $y = 40;
            // Scale down for ribbon strip
            $nw = (int) ($nw * 0.75);
            $nh = (int) ($nh * 0.75);
        } elseif ($card->template === CardTemplate::Studio) {
            $x = 40;
            $y = 200;
        } elseif ($card->template === CardTemplate::Monogram) {
            // Skip logo plate over monogram circle — place top-right instead
            $x = self::WIDTH - $nw - 48;
            $y = 40;
        }

        $plate = imagecolorallocatealpha($img, 255, 255, 255, 25);
        imagefilledrectangle($img, $x - $pad, $y - $pad, $x + $nw + $pad, $y + $nh + $pad, $plate);
        imagecopyresampled($img, $logo, $x, $y, 0, 0, $nw, $nh, $lw, $lh);
        imagedestroy($logo);
    }
}
