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
        ['group' => 'site', 'key' => 'logo', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'tagline', 'value' => 'Free Online Calculators for Construction, Finance, Health & More', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'support_email', 'value' => 'support@calculatorhub.com', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'default_locale', 'value' => 'en', 'type' => 'string', 'is_public' => true],
        ['group' => 'site', 'key' => 'enable_ads', 'value' => '1', 'type' => 'boolean', 'is_public' => false],
        ['group' => 'site', 'key' => 'enable_ai', 'value' => '1', 'type' => 'boolean', 'is_public' => false],

        // SEO
        ['group' => 'seo', 'key' => 'default_meta_title', 'value' => 'Calculator Hub - Free Online Calculators for Every Need', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'default_meta_description', 'value' => 'Free, accurate online calculators for construction, finance, health, education, business, unit conversion and engineering. Instant results with clear formulas and examples.', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'default_meta_keywords', 'value' => 'free calculator, online calculator, SIP calculator, EMI calculator, Nepal calculator', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'home_title', 'value' => 'AI Calculator Hub — Smart Calculators for Everyday Life', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'home_description', 'value' => 'Free, accurate, AI-powered calculators for finance, health, construction, math and more. Fast, ad-light, and mobile friendly.', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'publisher_name', 'value' => 'CalchubNepal', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'theme_color', 'value' => '#0B6E4F', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'schema_currency', 'value' => 'USD', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'twitter_site', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'ga_measurement_id', 'value' => 'G-ZG8HCJW6ET', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'gtm_container_id', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'google_site_verification', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'bing_site_verification', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'facebook_pixel_id', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'default_og_image', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'calculator_title_template', 'value' => '{title} — Free Online Calculator | {site}', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'calculator_description_template', 'value' => 'Use the free {title} to get instant results. Part of {category} tools on {site}. {description}', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'blog_title_template', 'value' => '{title} | {site} Blog', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'blog_description_template', 'value' => '{description}', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'category_title_template', 'value' => '{title} Calculators — Free Online Tools | {site}', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'category_description_template', 'value' => 'Browse free {title} calculators. {description}', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'header_scripts', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'footer_scripts', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'seo', 'key' => 'local_business_enabled', 'value' => '0', 'type' => 'boolean', 'is_public' => false],
        ['group' => 'seo', 'key' => 'business_phone', 'value' => '', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'business_address', 'value' => 'Kathmandu, Nepal', 'type' => 'string', 'is_public' => true],
        ['group' => 'seo', 'key' => 'business_country', 'value' => 'NP', 'type' => 'string', 'is_public' => true],

        // Ads (placeholders ignored until you set a real ca-pub-… / slot ID)
        ['group' => 'ads', 'key' => 'adsense_client_id', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'ads', 'key' => 'header_slot', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'ads', 'key' => 'sidebar_slot', 'value' => '', 'type' => 'string', 'is_public' => false],
        ['group' => 'ads', 'key' => 'auto_ads', 'value' => '0', 'type' => 'boolean', 'is_public' => false],

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
