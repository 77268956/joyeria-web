<?php

namespace App\Services;

use App\Enums\MetalPriceSource;
use App\Models\Metal;
use App\Models\MetalPrice;
use App\Models\Store;
use App\Models\User;
use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\MetalPriceQuote;

/**
 * Orchestrates metal-price ingestion and manual overrides.
 *
 * It never performs valuation maths itself: every conversion delegates to
 * {@see PricingCalculator}, and rows are immutable snapshots in `metal_prices`.
 */
class MetalPriceService
{
    public function __construct(
        protected PricingCalculator $calculator,
        protected MetalPriceProviderInterface $provider,
    ) {}

    /**
     * The current (most recent) price for a metal, honouring the store scope.
     */
    public function currentForMetal(Metal $metal): ?MetalPrice
    {
        return MetalPrice::where('metal_id', $metal->id)
            ->latest('created_at')
            ->latest('id')
            ->first();
    }

    /**
     * The current price for a metal identified by its code (GOLD, SILVER…).
     */
    public function currentByCode(string $code): ?MetalPrice
    {
        $metal = Metal::where('code', strtoupper($code))->first();

        return $metal ? $this->currentForMetal($metal) : null;
    }

    /**
     * Persist a price computed from an external spot quote.
     */
    public function recordFromQuote(Store $store, Metal $metal, MetalPriceQuote $quote, ?int $userId = null): MetalPrice
    {
        $exchangeRate = $this->exchangeRate();
        $pricePerGram = $this->calculator->pricePerGram($quote->spotPerOunce, $exchangeRate);

        return $this->record($store, $metal, $pricePerGram, MetalPriceSource::Api, [
            'spot_per_ounce' => $quote->spotPerOunce,
            'exchange_rate' => $exchangeRate,
            'provider' => $quote->provider,
        ], $userId);
    }

    /**
     * Fix a price manually with a mandatory reason (audited).
     */
    public function setManual(Store $store, Metal $metal, string $pricePerGram, string $reason, User $user): MetalPrice
    {
        return $this->record($store, $metal, $pricePerGram, MetalPriceSource::Manual, [
            'reason' => $reason,
        ], $user->id);
    }

    /**
     * Fetch and store the current price for a single metal. Null if unavailable.
     */
    public function refreshMetal(Store $store, Metal $metal, ?int $userId = null): ?MetalPrice
    {
        $quote = $this->provider->fetch($metal->code);

        if ($quote === null) {
            return null;
        }

        return $this->recordFromQuote($store, $metal, $quote, $userId);
    }

    /**
     * Refresh every precious metal for one store (or all active stores).
     *
     * @return array{updated: int, failed: array<int, string>}
     */
    public function refreshAll(?Store $store = null): array
    {
        $stores = $store ? collect([$store]) : Store::where('is_active', true)->get();
        $metals = Metal::where('is_precious', true)->orderBy('code')->get();

        $updated = 0;
        $failed = [];

        foreach ($stores as $currentStore) {
            foreach ($metals as $metal) {
                if ($this->refreshMetal($currentStore, $metal) !== null) {
                    $updated++;
                } else {
                    $failed[] = $metal->code;
                }
            }
        }

        return ['updated' => $updated, 'failed' => array_values(array_unique($failed))];
    }

    /**
     * Insert an immutable price row.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function record(Store $store, Metal $metal, string $pricePerGram, MetalPriceSource $source, array $attributes = [], ?int $userId = null): MetalPrice
    {
        return MetalPrice::create(array_merge([
            'store_id' => $store->id,
            'metal_id' => $metal->id,
            'price_per_gram' => $pricePerGram,
            'source' => $source,
            'price_date' => now()->toDateString(),
            'set_by' => $userId,
        ], $attributes));
    }

    /**
     * Configured FX multiplier from the provider currency to the local currency.
     */
    protected function exchangeRate(): string
    {
        return (string) config('services.metal_prices.exchange_rate', '1');
    }
}
