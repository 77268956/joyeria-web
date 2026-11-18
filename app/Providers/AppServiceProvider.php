<?php

namespace App\Providers;

use App\Models\Store;
use App\Models\SystemSetting;
use App\Services\CashSessionService;
use App\Services\MetalPriceService;
use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\Providers\FallbackChainProvider;
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

            $metalsDev = new MetalsDevProvider(
                apiKey: config('services.metal_prices.metals_dev.key'),
                endpoint: (string) config('services.metal_prices.metals_dev.endpoint'),
                currency: $currency,
            );

            $goldApi = new GoldApiProvider(
                apiKey: config('services.metal_prices.goldapi.key'),
                endpoint: (string) config('services.metal_prices.goldapi.endpoint'),
                currency: $currency,
            );

            // Try Metals.dev first, then GoldAPI.io, then last DB price.
            return new FallbackChainProvider($metalsDev, $goldApi);
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

            $storeId = Auth::user()?->store_id;
            $systemSettings = $storeId !== null
                ? SystemSetting::query()->where('store_id', $storeId)->first()
                : null;
            $systemSettings ??= SystemSetting::query()->orderBy('id')->first();

            $view->with([
                'goldMetalPrice' => $service->currentByCode('GOLD'),
                'silverMetalPrice' => $service->currentByCode('SILVER'),
                'openCashSession' => $cash->currentSession(Auth::user()),
                'systemSettings' => $systemSettings,
            ]);
        });

        View::composer(['layouts.auth', 'auth.login'], function ($view): void {
            $view->with(
                'systemSettings',
                SystemSetting::query()->orderBy('id')->first(),
            );
        });

        View::composer(['layouts.public', 'public.contact', 'public.store'], function ($view): void {
            $store = Store::publicSite();

            $view->with([
                'publicStore' => $store,
                'publicSettings' => $store?->systemSettings,
            ]);
        });
    }
}
