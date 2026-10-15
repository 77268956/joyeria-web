<?php

namespace App\Services\Pricing\Providers;

use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\MetalPriceQuote;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Spot-price provider backed by the Metals.dev REST API.
 *
 * @see https://metals.dev/
 */
class MetalsDevProvider implements MetalPriceProviderInterface
{
    /**
     * Map the store metal code to the metals.dev response key.
     *
     * @var array<string, string>
     */
    private const KEYS = [
        'GOLD' => 'gold',
        'SILVER' => 'silver',
    ];

    public function __construct(
        protected ?string $apiKey,
        protected string $endpoint,
        protected string $currency = 'USD',
        protected int $timeout = 15,
        protected int $retries = 3,
    ) {}

    public function fetch(string $metalCode): ?MetalPriceQuote
    {
        $key = self::KEYS[strtoupper($metalCode)] ?? null;

        if ($key === null || blank($this->apiKey)) {
            return null;
        }

        try {
            $response = $this->request()->get($this->endpoint, [
                'api_key' => $this->apiKey,
                'currency' => $this->currency,
                'unit' => 'toz',
            ]);

            $price = $response->json("metals.{$key}");

            if ($response->failed() || ! is_numeric($price)) {
                return null;
            }

            return new MetalPriceQuote(
                metalCode: strtoupper($metalCode),
                spotPerOunce: (string) $price,
                currency: $this->currency,
                provider: $this->name(),
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function name(): string
    {
        return 'Metals.dev';
    }

    /**
     * Build the HTTP request with retries.
     */
    protected function request(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->retry($this->retries, 500, throw: false)
            ->acceptJson();
    }
}
