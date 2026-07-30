<?php

namespace App\Support;

/**
 * Shared helpers for SEO-friendly images (alt, lazy, dimensions).
 */
class ImageSeo
{
    /**
     * Build safe image attributes for public pages.
     *
     * @param  array{alt?: string, width?: int, height?: int, lazy?: bool, class?: string, fetchpriority?: string}  $options
     * @return array<string, string|int>
     */
    public static function attrs(string $fallbackAlt, array $options = []): array
    {
        $alt = trim((string) ($options['alt'] ?? $fallbackAlt));
        if ($alt === '') {
            $alt = config('app.name', 'Calculator Hub');
        }

        $attrs = [
            'alt' => $alt,
            'decoding' => 'async',
        ];

        if (! empty($options['width'])) {
            $attrs['width'] = (int) $options['width'];
        }
        if (! empty($options['height'])) {
            $attrs['height'] = (int) $options['height'];
        }

        $lazy = $options['lazy'] ?? true;
        if ($lazy && ($options['fetchpriority'] ?? null) !== 'high') {
            $attrs['loading'] = 'lazy';
        }

        if (! empty($options['fetchpriority'])) {
            $attrs['fetchpriority'] = (string) $options['fetchpriority'];
        }

        if (! empty($options['class'])) {
            $attrs['class'] = (string) $options['class'];
        }

        return $attrs;
    }

    /**
     * Render HTML attribute string.
     *
     * @param  array{alt?: string, width?: int, height?: int, lazy?: bool, class?: string, fetchpriority?: string}  $options
     */
    public static function attributeString(string $fallbackAlt, array $options = []): string
    {
        $parts = [];
        foreach (self::attrs($fallbackAlt, $options) as $key => $value) {
            $parts[] = $key.'="'.e((string) $value).'"';
        }

        return implode(' ', $parts);
    }
}
