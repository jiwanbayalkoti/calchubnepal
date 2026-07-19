<?php

namespace App\Services\Settings;

/**
 * Resolves admin Settings (DB) with .env / config fallbacks for public site use.
 */
class AppSettings
{
    public function __construct(protected SettingsService $settings)
    {
    }

    public function get(string $group, string $key, mixed $default = null): mixed
    {
        return $this->settings->get($group, $key, $default);
    }

    public function siteName(): string
    {
        return $this->filledString('site', 'name')
            ?? config('app.name')
            ?? 'AI Calculator Hub';
    }

    public function tagline(): string
    {
        return $this->filledString('site', 'tagline')
            ?? 'Free, accurate, AI-assisted calculators for finance, health, construction, math and everyday life.';
    }

    public function supportEmail(): string
    {
        return $this->filledString('site', 'support_email')
            ?? (string) config('calculator_hub.contact.email', 'support@aicalculatorhub.com');
    }

    public function location(): string
    {
        return (string) config('calculator_hub.contact.location', 'Kathmandu, Nepal');
    }

    public function defaultLocale(): string
    {
        $locale = $this->filledString('site', 'default_locale')
            ?? (string) config('calculator_hub.default_locale', config('app.locale', 'en'));

        $supported = (array) config('calculator_hub.locales', ['en']);

        return in_array($locale, $supported, true) ? $locale : (string) config('calculator_hub.default_locale', 'en');
    }

    public function adsEnabled(): bool
    {
        return $this->bool('site', 'enable_ads', true);
    }

    public function aiEnabled(): bool
    {
        return $this->bool('site', 'enable_ai', true);
    }

    public function defaultMetaTitle(): string
    {
        return $this->filledString('seo', 'default_meta_title')
            ?? $this->siteName().' — Free Online Calculators';
    }

    public function defaultMetaDescription(): string
    {
        return $this->filledString('seo', 'default_meta_description')
            ?? $this->tagline();
    }

    public function homeTitle(): string
    {
        return $this->filledString('seo', 'home_title')
            ?? $this->defaultMetaTitle();
    }

    public function homeDescription(): string
    {
        return $this->filledString('seo', 'home_description')
            ?? $this->defaultMetaDescription();
    }

    public function adsenseClientId(): ?string
    {
        $fromSettings = $this->filledString('ads', 'adsense_client_id');
        if ($fromSettings && ! $this->isPlaceholderId($fromSettings)) {
            return $fromSettings;
        }

        $fromConfig = config('calculator_hub.adsense.client_id');

        return filled($fromConfig) && ! $this->isPlaceholderId((string) $fromConfig)
            ? (string) $fromConfig
            : null;
    }

    /**
     * AdSense unit slot for a position (header / sidebar).
     */
    public function adsenseSlot(string $position): ?string
    {
        $key = match ($position) {
            'header', 'footer' => 'header_slot',
            'sidebar' => 'sidebar_slot',
            default => null,
        };

        if ($key === null) {
            return null;
        }

        $slot = $this->filledString('ads', $key);

        return ($slot && ! $this->isPlaceholderId($slot)) ? $slot : null;
    }

    /**
     * Whether the AdSense loader script should be injected.
     */
    public function adsenseEnabled(): bool
    {
        if (! $this->adsEnabled()) {
            return false;
        }

        $clientId = $this->adsenseClientId();
        if (! filled($clientId)) {
            return false;
        }

        if ((bool) config('calculator_hub.adsense.enabled', false)) {
            return true;
        }

        // Allow enabling from admin when a real publisher ID is saved (even if .env flag is false).
        $fromSettings = $this->filledString('ads', 'adsense_client_id');

        return filled($fromSettings) && ! $this->isPlaceholderId($fromSettings);
    }

    public function adsenseAutoAds(): bool
    {
        return (bool) config('calculator_hub.adsense.auto_ads', false);
    }

    public function adsenseRequireConsent(): bool
    {
        return (bool) config('calculator_hub.adsense.require_consent', true);
    }

    public function aiDefaultProvider(): string
    {
        $provider = $this->filledString('ai', 'default_provider')
            ?? (string) config('calculator_hub.ai.default', 'openai');

        $known = array_keys((array) config('calculator_hub.ai.providers', []));

        return in_array($provider, $known, true) ? $provider : (string) config('calculator_hub.ai.default', 'openai');
    }

    public function aiDefaultModel(): ?string
    {
        return $this->filledString('ai', 'default_model');
    }

    /**
     * @return array<string, string> network => url
     */
    public function socialLinks(): array
    {
        $networks = ['facebook', 'twitter', 'linkedin', 'youtube', 'tiktok'];
        $links = [];

        foreach ($networks as $network) {
            $fromSettings = $this->filledString('social', $network);
            // Legacy admin key "TikTok"
            if ($network === 'tiktok' && ! $fromSettings) {
                $fromSettings = $this->filledString('social', 'TikTok');
            }

            $fromConfig = config("calculator_hub.contact.{$network}");

            $url = $fromSettings ?: (filled($fromConfig) ? (string) $fromConfig : null);
            if (filled($url) && ! str_starts_with($url, '#')) {
                $links[$network] = $url;
            }
        }

        return $links;
    }

    protected function filledString(string $group, string $key): ?string
    {
        $value = $this->settings->get($group, $key);
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    protected function bool(string $group, string $key, bool $default): bool
    {
        $value = $this->settings->get($group, $key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function isPlaceholderId(string $value): bool
    {
        return (bool) preg_match('/^0+$/', $value)
            || str_contains($value, '000000000')
            || $value === 'ca-pub-0000000000000000';
    }
}
