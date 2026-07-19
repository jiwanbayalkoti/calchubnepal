<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

/**
 * Seeds the platform's subscription tiers. Prices and limits are
 * intentionally modest placeholder values suitable for a production
 * launch; they can be tuned later through the admin panel without any
 * code changes since consumers read plans dynamically from the database.
 *
 * Safe to re-run: plans are upserted by slug.
 */
class SubscriptionPlanSeeder extends Seeder
{
    /**
     * @var array<int, array<string, mixed>>
     */
    protected const PLANS = [
        [
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Get started with unlimited access to every calculator on the platform, with standard ad support.',
            'price' => 0,
            'currency' => 'USD',
            'billing_period' => 'monthly',
            'features' => [
                'Unlimited calculator usage',
                'Save up to 5 calculations',
                'Standard ad-supported experience',
                'Email support',
            ],
            'api_rate_limit' => 0,
            'pdf_limit' => 1,
            'sort_order' => 1,
        ],
        [
            'name' => 'Premium Monthly',
            'slug' => 'premium-monthly',
            'description' => 'Go ad-free and unlock AI-powered explanations, unlimited saved calculations and PDF exports, billed monthly.',
            'price' => 9.99,
            'currency' => 'USD',
            'billing_period' => 'monthly',
            'features' => [
                'Ad-free experience',
                'AI-powered result explanations',
                'Unlimited saved calculations',
                'Unlimited PDF exports',
                'Priority email support',
            ],
            'api_rate_limit' => 0,
            'pdf_limit' => 0,
            'sort_order' => 2,
        ],
        [
            'name' => 'Premium Yearly',
            'slug' => 'premium-yearly',
            'description' => 'All Premium Monthly benefits at a discounted annual rate — over 15% savings versus paying monthly.',
            'price' => 99.00,
            'currency' => 'USD',
            'billing_period' => 'yearly',
            'features' => [
                'Ad-free experience',
                'AI-powered result explanations',
                'Unlimited saved calculations',
                'Unlimited PDF exports',
                'Priority email support',
                '2 months free versus monthly billing',
            ],
            'api_rate_limit' => 0,
            'pdf_limit' => 0,
            'sort_order' => 3,
        ],
        [
            'name' => 'API Pro',
            'slug' => 'api-pro',
            'description' => 'Built for developers and businesses that need programmatic access to every calculator via the REST API.',
            'price' => 29.99,
            'currency' => 'USD',
            'billing_period' => 'monthly',
            'features' => [
                'Everything in Premium Monthly',
                '100,000 API calls per month',
                '10 API keys per account',
                'Webhook support',
                'Dedicated developer support',
            ],
            'api_rate_limit' => 600,
            'pdf_limit' => 0,
            'sort_order' => 4,
        ],
    ];

    public function run(): void
    {
        foreach (self::PLANS as $plan) {
            SubscriptionPlan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    'name' => $plan['name'],
                    'description' => $plan['description'],
                    'price' => $plan['price'],
                    'currency' => $plan['currency'],
                    'billing_period' => $plan['billing_period'],
                    'features' => $plan['features'],
                    'api_rate_limit' => $plan['api_rate_limit'],
                    'pdf_limit' => $plan['pdf_limit'],
                    'is_active' => true,
                    'sort_order' => $plan['sort_order'],
                ]
            );
        }
    }
}
