<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds the baseline key/value application settings, grouped by area
 * (site, seo, ads, ai, social). These are consumed via Setting::group()
 * and drive the public-facing site chrome, SEO defaults, ad slots and
 * AI provider configuration.
 *
 * Safe to re-run: settings are upserted by their (group, key) pair.
 */
class SettingsSeeder extends Seeder
{
    /**
     * @var array<int, array{group: string, key: string, value: mixed, type: string, is_public: bool}>
     */
    protected const SETTINGS = [
        // Site
        ['group' => 'site', 'key' => 'name', 'value' => 'Calculator Hub', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'tagline', 'value' => 'Free Online Calculators for Construction, Finance, Health & More', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'support_email', 'value' => 'support@calculatorhub.com', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'default_locale', 'value' => 'en', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'enable_ads', 'value' => '1', 'type' => 'boolean', 'is_public' => false],
        ['group' => 'site', 'key' => 'enable_ai', 'value' => '1', 'type' => 'boolean', 'is_public' => false],

        // SEO
        ['group' => 'seo', 'key' => 'default_meta_title', 'value' => 'Calculator Hub - Free Online Calculators for Every Need', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'default_meta_description', 'value' => 'Free, accurate online calculators for construction, finance, health, education, business, unit conversion and engineering. Instant results with clear formulas and examples.', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'home_title', 'value' => 'AI Calculator Hub — Smart Calculators for Everyday Life', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'home_description', 'value' => 'Free, accurate, AI-powered calculators for finance, health, construction, math and more. Fast, ad-light, and mobile friendly.', 'type' => 'string', 'is_public' => true],

        // Ads (placeholders ignored until you set a real ca-pub-… / slot ID)
        ['group' => 'ads', 'key' => 'adsense_client_id', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'ads', 'key' => 'header_slot', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'ads', 'key' => 'sidebar_slot', 'value' => '', 'type' => 'string', 'is_public' => false],

        // AI
        ['group' => 'ai', 'key' => 'default_provider', 'value' => 'openai', 'type' => 'string', 'is_public' => false],
        ['group' => 'ai', 'key' => 'default_model', 'value' => 'gpt-4o-mini', 'type' => 'string', 'is_public' => false],

        // Social (leave empty to hide icons; or set full https URLs)
        ['group' => 'social', 'key' => 'facebook', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'social', 'key' => 'twitter', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'social', 'key' => 'linkedin', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'social', 'key' => 'youtube', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'social', 'key' => 'tiktok', 'value' => '', 'type' => 'string', 'is_public' => true],
    ];

    public function run(): void
    {
        foreach (self::SETTINGS as $setting) {
            Setting::query()->updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'is_public' => $setting['is_public'],
                ]
            );
        }
    }
}
