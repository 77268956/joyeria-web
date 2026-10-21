<?php

namespace App\Services\Pricing\Providers;

use App\Models\MetalPrice;
use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\MetalPriceQuote;
use Illuminate\Support\Facades\Log;

/**
 * Composite provider that tries each delegate in order and returns the first
 * successful quote. When all external providers fail it falls back to the last
 * price stored in the database, so the application always has a usable value.
 */
class FallbackChainProvider implements MetalPriceProviderInterface
{
    /** @var MetalPriceProviderInterface[] */
    private array $providers;

    public function __construct(MetalPriceProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    public function fetch(string $metalCode): ?MetalPriceQuote
    {
        foreach ($this->providers as $provider) {
            $quote = $provider->fetch($metalCode);

            if ($quote !== null) {
                return $quote;
            }

            Log::warning('MetalPriceProvider failed, trying next in chain', [
                'provider' => $provider->name(),
                'metal' => $metalCode,
            ]);
        }

        // All external providers exhausted — fall back to the last DB price.
        return $this->lastStoredQuote($metalCode);
    }

    public function name(): string
    {
        $names = array_map(fn (MetalPriceProviderInterface $p) => $p->name(), $this->providers);

        return implode(' → ', $names).' → BD';
    }

    /**
     * Build a synthetic quote from the most recent price row in the database.
     * Returns null only when the database has no price at all for this metal.
     */
    private function lastStoredQuote(string $metalCode): ?MetalPriceQuote
    {
        $row = MetalPrice::whereHas('metal', fn ($q) => $q->where('code', strtoupper($metalCode)))
            ->latest('created_at')
            ->latest('id')
            ->first();

        if ($row === null) {
            return null;
        }

        Log::warning('All external metal price providers failed; using last DB price', [
            'metal' => $metalCode,
            'price_per_gram' => $row->price_per_gram,
            'price_date' => $row->price_date,
        ]);

        // Re-express stored price_per_gram as a synthetic spot-per-ounce so the
        // calling service can apply its normal exchange-rate conversion path.
        // 1 troy ounce = 31.1034768 grams.
        $spotPerOunce = bcmul((string) $row->price_per_gram, '31.1034768', 10);

        return new MetalPriceQuote(
            metalCode: strtoupper($metalCode),
            spotPerOunce: $spotPerOunce,
            currency: (string) config('services.metal_prices.currency', 'USD'),
            provider: 'BD (último guardado '.$row->price_date.')',
        );
    }
}
