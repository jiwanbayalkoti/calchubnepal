<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Calculator;
use App\Models\CalculatorCategory;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use Symfony\Component\HttpFoundation\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('calc_hub:sitemap:xml', 3600, function () {
            $sitemap = Sitemap::create()
                ->add(Url::create(route('home'))->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
                ->add(Url::create(route('calculators.index'))->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
                ->add(Url::create(route('categories.index'))->setPriority(0.7))
                ->add(Url::create(route('blog.index'))->setPriority(0.6))
                ->add(Url::create(route('pricing'))->setPriority(0.5))
                ->add(Url::create(route('about'))->setPriority(0.3))
                ->add(Url::create(route('contact'))->setPriority(0.3))
                ->add(Url::create(route('privacy'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
                ->add(Url::create(route('terms'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
                ->add(Url::create(route('cookies'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
                ->add(Url::create(route('disclaimer'))->setPriority(0.2)->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY))
                ->add(Url::create(route('sitemap'))->setPriority(0.3)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));

            CalculatorCategory::query()->active()->each(function (CalculatorCategory $category) use ($sitemap) {
                $sitemap->add(Url::create(route('categories.show', $category))->setPriority(0.7));
            });

            Calculator::query()->active()->each(function (Calculator $calculator) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('calculators.show', $calculator))
                        ->setPriority(0.8)
                        ->setLastModificationDate($calculator->updated_at)
                );
            });

            BlogPost::query()->published()->each(function (BlogPost $post) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('blog.show', $post))
                        ->setPriority(0.5)
                        ->setLastModificationDate($post->updated_at)
                );
            });

            return $sitemap->render();
        });

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
