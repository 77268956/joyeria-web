<?php

namespace App\Providers;

use App\Models\Store;
use App\Services\CashSessionService;
use App\Services\MetalPriceService;
use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\Providers\GoldApiProvider;
use App\Services\Pricing\Providers\MetalsDevProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MetalPriceProviderInterface::class, function (): MetalPriceProviderInterface {
            $currency = (string) config('services.metal_prices.currency', 'USD');

            return match (config('services.metal_prices.provider')) {
                'metals_dev' => new MetalsDevProvider(
                    apiKey: config('services.metal_prices.metals_dev.key'),
                    endpoint: (string) config('services.metal_prices.metals_dev.endpoint'),
                    currency: $currency,
                ),
                default => new GoldApiProvider(
                    apiKey: config('services.metal_prices.goldapi.key'),
                    endpoint: (string) config('services.metal_prices.goldapi.endpoint'),
                    currency: $currency,
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());

        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        View::composer('layouts.admin', function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $service = $this->app->make(MetalPriceService::class);
            $cash = $this->app->make(CashSessionService::class);

            $view->with([
                'goldMetalPrice' => $service->currentByCode('GOLD'),
                'silverMetalPrice' => $service->currentByCode('SILVER'),
                'openCashSession' => $cash->currentSession(Auth::user()),
            ]);
        });

        View::composer('layouts.public', function ($view): void {
            $store = Store::publicSite();

            $view->with([
                'publicStore' => $store,
                'publicSettings' => $store?->systemSettings,
            ]);
        });
    }
}
