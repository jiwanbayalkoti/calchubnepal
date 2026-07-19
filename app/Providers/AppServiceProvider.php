<?php

namespace App\Providers;

use App\Contracts\Repositories\CalculatorRepositoryInterface;
use App\Contracts\Services\CalculatorServiceInterface;
use App\Models\BlogPost;
use App\Models\Calculator;
use App\Policies\BlogPostPolicy;
use App\Policies\CalculatorPolicy;
use App\Repositories\CalculatorRepository;
use App\Services\Ai\AiService;
use App\Services\Ai\AiServiceInterface;
use App\Services\Calculators\CalculatorEngineService;
use App\Services\Calculators\CalculatorRegistry;
use App\Services\Settings\AppSettings;
use App\Services\Settings\SettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CalculatorRegistry::class);

        $this->app->bind(CalculatorRepositoryInterface::class, CalculatorRepository::class);

        $this->app->bind(CalculatorServiceInterface::class, CalculatorEngineService::class);

        $this->app->singleton(SettingsService::class);
        $this->app->singleton(AppSettings::class);

        $this->app->bind(AiServiceInterface::class, function ($app) {
            $config = (array) config('calculator_hub.ai', []);
            $hub = $app->make(AppSettings::class);
            $config['default'] = $hub->aiDefaultProvider();
            if ($model = $hub->aiDefaultModel()) {
                $provider = $config['default'];
                if (isset($config['providers'][$provider])) {
                    $config['providers'][$provider]['model'] = $model;
                }
            }

            return new AiService($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Gate::policy(Calculator::class, CalculatorPolicy::class);
        Gate::policy(BlogPost::class, BlogPostPolicy::class);

        // Help Windows/XAMPP PHP verify HTTPS (Google OAuth, AI APIs, etc.).
        $caBundle = base_path('certificates/cacert.pem');
        if (is_file($caBundle)) {
            if (! ini_get('curl.cainfo')) {
                @ini_set('curl.cainfo', $caBundle);
            }
            if (! ini_get('openssl.cafile')) {
                @ini_set('openssl.cafile', $caBundle);
            }
            putenv('CURL_CA_BUNDLE='.$caBundle);
            putenv('SSL_CERT_FILE='.$caBundle);
        }

        View::composer('*', function ($view) {
            $view->with('hub', app(AppSettings::class));
        });
    }
}
