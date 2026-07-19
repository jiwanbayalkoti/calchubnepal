<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Supported Locales
    |--------------------------------------------------------------------------
    */

    'locales' => ['en', 'ne'],

    'default_locale' => env('CALCULATOR_HUB_DEFAULT_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'prefix' => 'calc_hub',
        'ttl' => [
            'settings' => (int) env('CALC_HUB_CACHE_TTL_SETTINGS', 3600),
            'seo' => (int) env('CALC_HUB_CACHE_TTL_SEO', 3600),
            'calculators' => (int) env('CALC_HUB_CACHE_TTL_CALCULATORS', 1800),
            'translations' => (int) env('CALC_HUB_CACHE_TTL_TRANSLATIONS', 3600),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Providers
    |--------------------------------------------------------------------------
    |
    | The AI service layer (App\Services\Ai) never talks to a provider's SDK
    | or HTTP API directly from controllers. Switching providers is a
    | configuration-only change - update `default` (or pass `provider` in
    | the call options) and the matching provider block below.
    |
    */

    'ai' => [
        'default' => env('AI_DEFAULT_PROVIDER', 'openai'),

        'default_temperature' => 0.7,
        'default_max_tokens' => 1024,

        'providers' => [
            'openai' => [
                'api_key' => env('OPENAI_API_KEY'),
                'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
                'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
                'timeout' => (int) env('OPENAI_TIMEOUT', 30),
            ],

            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
                'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
                'timeout' => (int) env('GEMINI_TIMEOUT', 30),
            ],

            'claude' => [
                'api_key' => env('CLAUDE_API_KEY'),
                'base_url' => env('CLAUDE_BASE_URL', 'https://api.anthropic.com/v1'),
                'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet-latest'),
                'timeout' => (int) env('CLAUDE_TIMEOUT', 30),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google AdSense
    |--------------------------------------------------------------------------
    */

    'adsense' => [
        'enabled' => (bool) env('ADSENSE_ENABLED', false),
        'client_id' => env('ADSENSE_CLIENT_ID'),
        'auto_ads' => (bool) env('ADSENSE_AUTO_ADS', false),
        /*
         * When true, AdSense script loads only after the user accepts cookies.
         * Keep true for EEA/UK-friendly defaults; set false only if you use
         * non-personalized ads and accept the compliance trade-off.
         */
        'require_consent' => (bool) env('ADSENSE_REQUIRE_CONSENT', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ad demo placeholders
    |--------------------------------------------------------------------------
    |
    | When true, empty ad slots show demo placeholders. Keep false in production
    | for AdSense review (empty slots stay empty instead of fake banners).
    |
    */
    'ads_demo_mode' => (bool) env('ADS_DEMO_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Public contact / social (trust signals)
    |--------------------------------------------------------------------------
    */
    'contact' => [
        'email' => env('CONTACT_EMAIL', 'support@aicalculatorhub.com'),
        'location' => env('CONTACT_LOCATION', 'Kathmandu, Nepal'),
        'facebook' => env('SOCIAL_FACEBOOK'),
        'twitter' => env('SOCIAL_TWITTER'),
        'linkedin' => env('SOCIAL_LINKEDIN'),
        'youtube' => env('SOCIAL_YOUTUBE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Advertisement positions & standard creative sizes (IAB-aligned)
    |--------------------------------------------------------------------------
    |
    | Upload images at the recommended width × height for each slot so banners
    | are not cropped awkwardly on the public site.
    |
    */
    'ads' => [
        'max_upload_kb' => 1024,

        'positions' => [
            'header' => [
                'label' => 'Header',
                'variant' => 'leaderboard',
                'width' => 728,
                'height' => 90,
                'size_label' => '728 × 90 px',
                'iab_name' => 'Leaderboard',
                'placement' => 'Below the site navbar (on home: under the hero). Full-width horizontal strip.',
                'alternates' => ['970 × 90'],
                'hint' => 'Create a wide horizontal banner (728×90). Avoid tall images — they will be cropped.',
            ],
            'sidebar' => [
                'label' => 'Sidebar',
                'variant' => 'box',
                'width' => 300,
                'height' => 250,
                'size_label' => '300 × 250 px',
                'iab_name' => 'Medium Rectangle',
                'placement' => 'Right column on calculator, category, and blog pages.',
                'alternates' => ['300 × 300', '336 × 280'],
                'hint' => 'Use a square-ish box ad (300×250). Best for product or promo creatives.',
            ],
            'footer' => [
                'label' => 'Footer',
                'variant' => 'leaderboard',
                'width' => 728,
                'height' => 90,
                'size_label' => '728 × 90 px',
                'iab_name' => 'Leaderboard',
                'placement' => 'Above the site footer on public pages.',
                'alternates' => ['970 × 90'],
                'hint' => 'Same as header — wide 728×90 banner.',
            ],
            'sticky' => [
                'label' => 'Sticky',
                'variant' => 'sticky',
                'width' => 160,
                'height' => 600,
                'size_label' => '160 × 600 px',
                'iab_name' => 'Wide Skyscraper',
                'placement' => 'Fixed bottom-right floating unit (hidden on mobile). Display width ~180px.',
                'alternates' => ['300 × 600', '180 × 360'],
                'hint' => 'Tall vertical creative (160×600). Keep important text in the center — edges may crop slightly.',
            ],
            'in_content' => [
                'label' => 'In Content',
                'variant' => 'inline',
                'width' => 728,
                'height' => 90,
                'size_label' => '728 × 90 px',
                'iab_name' => 'Leaderboard / Mobile Banner',
                'placement' => 'Inside the calculator page content area.',
                'alternates' => ['320 × 100', '320 × 50'],
                'hint' => 'Horizontal strip (728×90). On phones, 320×100 also works well.',
            ],
            'between_results' => [
                'label' => 'Between Results',
                'variant' => 'inline',
                'width' => 728,
                'height' => 90,
                'size_label' => '728 × 90 px',
                'iab_name' => 'Leaderboard / Mobile Banner',
                'placement' => 'Between calculator form and result output.',
                'alternates' => ['320 × 100'],
                'hint' => 'Horizontal strip after results (728×90). Keep the message short and readable.',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Premium / Subscription Defaults
    |--------------------------------------------------------------------------
    |
    | Fallback feature flags and limits used when a user has no active
    | subscription plan (or the plan record is missing a value).
    |
    */

    'premium' => [
        'features' => [
            'pdf_export' => true,
            'ad_free' => true,
            'unlimited_saved_calculations' => true,
            'priority_support' => false,
            'api_access' => true,
        ],

        'free_plan' => [
            'pdf_limit' => (int) env('FREE_PLAN_PDF_LIMIT', 3),
            'api_rate_limit' => (int) env('FREE_PLAN_API_RATE_LIMIT', 0),
            'saved_calculations_limit' => (int) env('FREE_PLAN_SAVED_CALCULATIONS_LIMIT', 5),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Blog
    |--------------------------------------------------------------------------
    */

    'blog' => [
        'reading_speed_wpm' => 200,
    ],

];
