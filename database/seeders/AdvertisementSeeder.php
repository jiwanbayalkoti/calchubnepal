<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Seeds polished slideshow banners for every public ad position.
 * Content is plain text — the public Blade slide template renders the design.
 */
class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            // Retire old sample matrix so slideshow uses the new promo set.
            Advertisement::query()
                ->where('slug', 'like', 'sample-%')
                ->update(['is_active' => false]);

            $sort = 0;

            foreach ($this->banners() as $position => $slides) {
                foreach ($slides as $index => $slide) {
                    $sort++;
                    $slug = "promo-{$position}-".($index + 1);

                    Advertisement::query()->updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $slide['title'],
                            'position' => $position,
                            'ad_type' => 'banner',
                            'content' => $slide['text'],
                            'image' => null,
                            'link_url' => $slide['url'],
                            'adsense_code' => null,
                            'is_active' => true,
                            'start_at' => now()->subDay(),
                            'end_at' => now()->addYear(),
                            'sort_order' => $sort,
                        ]
                    );
                }
            }
        });

        foreach (['header', 'sidebar', 'sticky', 'footer', 'in_content', 'between_results'] as $position) {
            Cache::forget("calc_hub:ads:{$position}");
        }
    }

    /**
     * @return array<string, list<array{title: string, text: string, url: string}>>
     */
    protected function banners(): array
    {
        $pricing = url('/pricing');
        $calculators = url('/calculators');
        $register = url('/register');

        return [
            'header' => [
                [
                    'title' => 'Go Premium — unlimited saves & PDF',
                    'text' => 'Remove limits, export reports, and unlock priority AI explanations.',
                    'url' => $pricing,
                ],
                [
                    'title' => '182+ calculators. One smart hub.',
                    'text' => 'Finance, construction, Nepal tax, fitness and more — free to start.',
                    'url' => $calculators,
                ],
                [
                    'title' => 'Build faster with AI explanations',
                    'text' => 'Every result can be explained in plain language. Try it free today.',
                    'url' => $register,
                ],
            ],
            'sidebar' => [
                [
                    'title' => 'Save your calculations',
                    'text' => 'Create a free account and keep results for later.',
                    'url' => $register,
                ],
                [
                    'title' => 'Nepal tax & land tools',
                    'text' => 'Income tax, VAT, Ropani/Aana and NEPSE brokerage in one place.',
                    'url' => url('/category/nepal'),
                ],
                [
                    'title' => 'Construction estimators',
                    'text' => 'Bricks, cement, RCC, rebar and house cost — plan with confidence.',
                    'url' => url('/category/construction'),
                ],
            ],
            'footer' => [
                [
                    'title' => 'Partner with Calculator Hub',
                    'text' => 'Reach builders, students and finance users with sponsored placements.',
                    'url' => url('/contact'),
                ],
                [
                    'title' => 'Need the API?',
                    'text' => 'Same formulas for web and future mobile apps. Ask us about API Pro.',
                    'url' => $pricing,
                ],
            ],
            'sticky' => [
                [
                    'title' => 'Upgrade',
                    'text' => 'Premium unlocks more saves.',
                    'url' => $pricing,
                ],
                [
                    'title' => 'Try EMI',
                    'text' => 'Plan loans in seconds.',
                    'url' => url('/calculator/emi-calculator'),
                ],
            ],
            'in_content' => [
                [
                    'title' => 'Tip: Save this result',
                    'text' => 'Logged-in users can bookmark and revisit calculations anytime.',
                    'url' => $register,
                ],
                [
                    'title' => 'Compare plans',
                    'text' => 'Free forever for basics. Premium when you need more.',
                    'url' => $pricing,
                ],
            ],
            'between_results' => [
                [
                    'title' => 'Export as PDF',
                    'text' => 'Premium members download clean reports of every calculation.',
                    'url' => $pricing,
                ],
                [
                    'title' => 'Ask AI to explain',
                    'text' => 'Tap AI Explain above for a plain-language breakdown.',
                    'url' => $calculators,
                ],
            ],
        ];
    }
}
