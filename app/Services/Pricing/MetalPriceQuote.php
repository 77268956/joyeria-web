<?php

namespace App\Services\Pricing;

/**
 * Immutable spot-price quote returned by a metal-price provider.
 *
 * Prices are expressed per troy ounce of pure metal in `currency`.
 */
final readonly class MetalPriceQuote
{
    public function __construct(
        public string $metalCode,
        public string $spotPerOunce,
        public string $currency,
        public string $provider,
    ) {}
}
