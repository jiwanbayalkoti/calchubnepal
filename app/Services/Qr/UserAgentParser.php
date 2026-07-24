<?php

namespace App\Services\Qr;

/**
 * Lightweight UA parser for scan analytics (no external dependency).
 */
class UserAgentParser
{
    /**
     * @return array{device: string, browser: string, os: string}
     */
    public function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        return [
            'device' => $this->device($ua),
            'browser' => $this->browser($ua),
            'os' => $this->os($ua),
        ];
    }

    public function device(string $ua): string
    {
        $lower = strtolower($ua);
        if ($lower === '') {
            return 'unknown';
        }
        if (str_contains($lower, 'tablet') || str_contains($lower, 'ipad')) {
            return 'tablet';
        }
        if (str_contains($lower, 'mobile') || str_contains($lower, 'android') || str_contains($lower, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function browser(string $ua): string
    {
        return match (true) {
            preg_match('/Edg\//i', $ua) === 1 => 'Edge',
            preg_match('/OPR\/|Opera/i', $ua) === 1 => 'Opera',
            preg_match('/SamsungBrowser/i', $ua) === 1 => 'Samsung Internet',
            preg_match('/Chrome\//i', $ua) === 1 && preg_match('/Chromium/i', $ua) !== 1 => 'Chrome',
            preg_match('/CriOS/i', $ua) === 1 => 'Chrome',
            preg_match('/Firefox\//i', $ua) === 1 || preg_match('/FxiOS/i', $ua) === 1 => 'Firefox',
            preg_match('/Safari\//i', $ua) === 1 && preg_match('/Chrome|Chromium|CriOS/i', $ua) !== 1 => 'Safari',
            preg_match('/MSIE|Trident/i', $ua) === 1 => 'IE',
            default => 'Other',
        };
    }

    public function os(string $ua): string
    {
        return match (true) {
            preg_match('/Windows NT/i', $ua) === 1 => 'Windows',
            preg_match('/Android/i', $ua) === 1 => 'Android',
            preg_match('/iPhone|iPad|iPod/i', $ua) === 1 => 'iOS',
            preg_match('/Mac OS X/i', $ua) === 1 => 'macOS',
            preg_match('/Linux/i', $ua) === 1 => 'Linux',
            preg_match('/CrOS/i', $ua) === 1 => 'ChromeOS',
            default => 'Other',
        };
    }
}
