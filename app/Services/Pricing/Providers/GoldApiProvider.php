<?php

namespace App\Services\Pricing\Providers;

use App\Services\Pricing\MetalPriceProviderInterface;
use App\Services\Pricing\MetalPriceQuote;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Spot-price provider backed by the GoldAPI.io REST API.
 *
 * @see https://www.goldapi.io/
 */
class GoldApiProvider implements MetalPriceProviderInterface
{
    /**
     * Map the store metal code to the provider symbol.
     *
     * @var array<string, string>
     */
    private const SYMBOLS = [
        'GOLD' => 'XAU',
        'SILVER' => 'XAG',
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
        $symbol = self::SYMBOLS[strtoupper($metalCode)] ?? null;

        if ($symbol === null || blank($this->apiKey)) {
            return null;
        }

        try {
            $response = $this->request()->get("{$this->endpoint}/{$symbol}/{$this->currency}");

            if ($response->failed() || ! is_numeric($response->json('price'))) {
                return null;
            }

            return new MetalPriceQuote(
                metalCode: strtoupper($metalCode),
                spotPerOunce: (string) $response->json('price'),
                currency: $this->currency,
                provider: $this->name(),
            );
        } catch (Throwable) {
            return null;
        }
    }

    public function name(): string
    {
        return 'GoldAPI.io';
    }

    /**
     * Build the HTTP request with retries and the provider auth header.
     */
    protected function request(): PendingRequest
    {
        return Http::withHeaders(['x-access-token' => $this->apiKey])
            ->timeout($this->timeout)
            ->retry($this->retries, 500, throw: false)
            ->acceptJson();
    }
}
