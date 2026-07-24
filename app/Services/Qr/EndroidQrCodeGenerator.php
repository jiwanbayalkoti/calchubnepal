<?php

namespace App\Services\Qr;

use App\Contracts\Qr\QrCodeGeneratorInterface;
use App\DTOs\Qr\QrGenerateOptions;
use App\DTOs\Qr\QrGenerateResult;
use App\Enums\Qr\QrErrorCorrection;
use App\Enums\Qr\QrFrameStyle;
use App\Enums\Qr\QrModuleStyle;
use App\Enums\Qr\QrOutputFormat;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PdfWriter;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\Writer\WebPWriter;
use InvalidArgumentException;

class EndroidQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function __construct(protected QrImageStyleProcessor $styles)
    {
    }

    public function generate(QrGenerateOptions $options): QrGenerateResult
    {
        if ($options->payload === '') {
            throw new InvalidArgumentException('QR payload is empty.');
        }

        if (! extension_loaded('gd')) {
            throw new InvalidArgumentException('PHP GD extension is required to generate QR codes. Enable ext-gd on the server.');
        }

        $needsRasterStyle = $options->frameStyle !== QrFrameStyle::None
            || $options->moduleStyle !== QrModuleStyle::Square
            || $options->eyeStyle->value !== 'square'
            || $options->format === QrOutputFormat::Jpg;

        $buildFormat = match ($options->format) {
            QrOutputFormat::Svg => QrOutputFormat::Svg,
            QrOutputFormat::Pdf => QrOutputFormat::Pdf,
            QrOutputFormat::Webp => $needsRasterStyle ? QrOutputFormat::Png : QrOutputFormat::Webp,
            default => QrOutputFormat::Png,
        };

        // Logo + advanced raster styles are applied on PNG first.
        if ($options->logoPath || $needsRasterStyle) {
            if ($options->format === QrOutputFormat::Svg) {
                $buildFormat = QrOutputFormat::Png;
            }
        }

        $writer = match ($buildFormat) {
            QrOutputFormat::Svg => new SvgWriter,
            QrOutputFormat::Pdf => new PdfWriter,
            QrOutputFormat::Webp => new WebPWriter,
            default => new PngWriter,
        };

        $errorLevel = $this->mapErrorCorrection($options->errorCorrection);
        if ($options->logoPath) {
            $errorLevel = ErrorCorrectionLevel::High;
        }

        $builderArgs = [
            'writer' => $writer,
            'data' => $options->payload,
            'encoding' => new Encoding('UTF-8'),
            'errorCorrectionLevel' => $errorLevel,
            'size' => $options->size,
            'margin' => $options->margin,
            'roundBlockSizeMode' => $options->moduleStyle === QrModuleStyle::Dots
                ? RoundBlockSizeMode::None
                : RoundBlockSizeMode::Margin,
            'foregroundColor' => $this->toColor($options->foreground),
            'backgroundColor' => $this->toColor($options->background),
        ];

        if ($options->logoPath && is_file($options->logoPath) && $buildFormat !== QrOutputFormat::Svg) {
            $builderArgs['logoPath'] = $options->logoPath;
            $builderArgs['logoResizeToWidth'] = $options->logoSize;
            $builderArgs['logoPunchoutBackground'] = true;
        }

        $result = (new Builder(...$builderArgs))->build();
        $binary = $result->getString();
        $mime = $result->getMimeType();
        $finalFormat = $options->format;

        if ($buildFormat === QrOutputFormat::Png && (
            $needsRasterStyle || $options->format === QrOutputFormat::Jpg || $options->format === QrOutputFormat::Webp || $options->format === QrOutputFormat::Pdf
        )) {
            if ($needsRasterStyle) {
                $binary = $this->styles->applyRasterStyles(
                    $binary,
                    $options->moduleStyle,
                    $options->eyeStyle,
                    $options->frameStyle,
                    $options->frameLabel,
                    $options->foreground,
                    $options->background,
                );
            }

            if ($options->format === QrOutputFormat::Jpg) {
                $binary = $this->styles->pngToJpg($binary);
                $mime = QrOutputFormat::Jpg->mimeType();
            } elseif ($options->format === QrOutputFormat::Webp) {
                $binary = $this->pngToWebp($binary);
                $mime = QrOutputFormat::Webp->mimeType();
            } elseif ($options->format === QrOutputFormat::Pdf) {
                $binary = $this->pngToPdf($binary, $options->size);
                $mime = QrOutputFormat::Pdf->mimeType();
            } elseif ($options->format === QrOutputFormat::Svg && $needsRasterStyle) {
                // Styled raster cannot stay SVG — return PNG instead.
                $finalFormat = QrOutputFormat::Png;
                $mime = QrOutputFormat::Png->mimeType();
            } else {
                $mime = QrOutputFormat::Png->mimeType();
            }
        }

        return new QrGenerateResult(
            payload: $options->payload,
            binary: $binary,
            format: $finalFormat,
            type: $options->type,
            size: $options->size,
            mimeType: $mime,
        );
    }

    protected function pngToWebp(string $pngBinary, int $quality = 85): string
    {
        $img = @imagecreatefromstring($pngBinary);
        if ($img === false) {
            throw new InvalidArgumentException('Unable to convert QR to WebP.');
        }
        if (! function_exists('imagewebp')) {
            imagedestroy($img);
            throw new InvalidArgumentException('WebP is not supported on this server.');
        }
        ob_start();
        imagewebp($img, null, $quality);
        $out = (string) ob_get_clean();
        imagedestroy($img);

        return $out;
    }

    protected function pngToPdf(string $pngBinary, int $size): string
    {
        if (! class_exists(\FPDF::class)) {
            throw new InvalidArgumentException('PDF support requires setasign/fpdf.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'qrpdf_');
        if ($tmp === false) {
            throw new InvalidArgumentException('Unable to create temp file for PDF.');
        }
        $pngPath = $tmp.'.png';
        file_put_contents($pngPath, $pngBinary);

        $mm = max(40, (int) round($size * 0.264583));
        $pdf = new \FPDF('P', 'mm', [$mm + 20, $mm + 20]);
        $pdf->AddPage();
        $pdf->Image($pngPath, 10, 10, $mm, $mm, 'PNG');
        $out = $pdf->Output('S');
        @unlink($pngPath);
        @unlink($tmp);

        return $out;
    }

    protected function mapErrorCorrection(QrErrorCorrection $level): ErrorCorrectionLevel
    {
        return match ($level) {
            QrErrorCorrection::Low => ErrorCorrectionLevel::Low,
            QrErrorCorrection::Medium => ErrorCorrectionLevel::Medium,
            QrErrorCorrection::Quartile => ErrorCorrectionLevel::Quartile,
            QrErrorCorrection::High => ErrorCorrectionLevel::High,
        };
    }

    protected function toColor(string $hex): Color
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6) {
            throw new InvalidArgumentException('Invalid color hex.');
        }

        return new Color(
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        );
    }
}
