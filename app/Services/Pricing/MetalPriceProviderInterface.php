<?php

namespace App\Services\Pricing;

/**
 * External spot-price provider contract.
 *
 * Implementations call an external API (GoldAPI.io, Metals.dev, …) and return
 * a normalised {@see MetalPriceQuote}, or null when the metal is unknown or
 * the provider could not be reached. Nothing else in the application talks to
 * an external price API directly.
 */
interface MetalPriceProviderInterface
{
    /**
     * Fetch the current spot price for the given metal code (GOLD, SILVER…).
     */
    public function fetch(string $metalCode): ?MetalPriceQuote;

    /**
     * Human readable provider name, stored with each price row.
     */
    public function name(): string;
}
