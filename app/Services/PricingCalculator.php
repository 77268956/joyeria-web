<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Single authorised place for every precious-metal valuation formula.
 *
 * All maths use BCMath string arithmetic — never floats — so results are
 * exact and reproducible to the cent. Money is rounded half-up to two
 * decimals; the per-gram price keeps four decimals.
 *
 * Formulas (see docs/02-trd.md §6.1):
 *   Gold:   amount = net_weight_g × price_per_gram(24k) × (karat / 24) × (1 − discount)
 *   Silver: amount = net_weight_g × price_per_gram(pure) × (fineness/1000) × (1 − discount)
 * Both collapse to: net_weight_g × price_per_gram(pure) × fineness × (1 − discount),
 * where `fineness` is the stored fineness (0.7500 for 18k, 0.9250 for ley 925…).
 */
class PricingCalculator
{
    /**
     * Grams contained in one troy ounce.
     */
    public const TROY_OUNCE_GRAMS = '31.1034768';

    /**
     * Convert a spot price per troy ounce into a price per gram.
     *
     * @param  string  $spotPerOunce  Spot price of the pure metal per troy ounce, in the source currency.
     * @param  string  $exchangeRate  Multiplier from the source currency to the local currency (default 1).
     * @return string Price per gram of pure metal, rounded to 4 decimals.
     */
    public function pricePerGram(string $spotPerOunce, string $exchangeRate = '1'): string
    {
        $this->assertNumeric($spotPerOunce, 'precio por onza');
        $this->assertNumeric($exchangeRate, 'tipo de cambio');

        $localPerOunce = bcmul($spotPerOunce, $exchangeRate, 10);

        return $this->round(bcdiv($localPerOunce, self::TROY_OUNCE_GRAMS, 10), 4);
    }

    /**
     * Value of the pure metal contained in a piece (before labour and margin).
     *
     * @param  string  $netWeightG  Net weight in grams (gross minus non-precious elements).
     * @param  string  $pricePerGramPure  Price of one gram of pure metal (24k / 1000).
     * @param  string  $fineness  Purity factor between 0 and 1 (e.g. 0.7500).
     * @return string Value rounded to 2 decimals.
     */
    public function metalValue(string $netWeightG, string $pricePerGramPure, string $fineness): string
    {
        return $this->round($this->rawMetalValue($netWeightG, $pricePerGramPure, $fineness), 2);
    }

    /**
     * Amount paid to a customer when buying a piece (PRD §7.1/§7.2).
     *
     * @param  string  $netWeightG  Net weight in grams.
     * @param  string  $pricePerGramPure  Price of one gram of pure metal.
     * @param  string  $fineness  Purity factor between 0 and 1.
     * @param  string  $discountPercent  Store discount applied on purchase (0–100).
     * @return string Amount rounded to 2 decimals.
     */
    public function purchaseAmount(string $netWeightG, string $pricePerGramPure, string $fineness, string $discountPercent): string
    {
        $this->assertNumeric($discountPercent, 'descuento');

        $discountFactor = bcsub('1', bcdiv($discountPercent, '100', 10), 10);
        $value = bcmul($this->rawMetalValue($netWeightG, $pricePerGramPure, $fineness), $discountFactor, 12);

        return $this->round($value, 2);
    }

    /**
     * Convert a gold karat value (10, 14, 18, 24) into its fineness factor.
     */
    public function finenessFromKarat(int $karat): string
    {
        if ($karat < 0 || $karat > 24) {
            throw new InvalidArgumentException('Los quilates deben estar entre 0 y 24.');
        }

        return $this->round(bcdiv((string) $karat, '24', 6), 4);
    }

    /**
     * Round a decimal string half-up to the given number of decimals.
     */
    public function round(string $value, int $scale = 2): string
    {
        $this->assertNumeric($value, 'valor');

        if ($scale < 0) {
            throw new InvalidArgumentException('La escala de redondeo no puede ser negativa.');
        }

        $negative = bccomp($value, '0', $scale + 4) < 0;
        $absolute = $negative ? bcmul($value, '-1', $scale + 4) : $value;
        $factor = '1'.str_repeat('0', $scale);

        // Add 0.5 then truncate toward zero => round half-up on the absolute value.
        $scaled = bcadd(bcmul($absolute, $factor, 4), '0.5', 0);
        $result = bcdiv($scaled, $factor, $scale);

        return $negative ? bcmul($result, '-1', $scale) : $result;
    }

    /**
     * Raw unrounded metal value, shared by the public valuation methods.
     */
    protected function rawMetalValue(string $netWeightG, string $pricePerGramPure, string $fineness): string
    {
        $this->assertNumeric($netWeightG, 'peso neto');
        $this->assertNumeric($pricePerGramPure, 'precio por gramo');
        $this->assertNumeric($fineness, 'pureza');

        return bcmul(bcmul($netWeightG, $pricePerGramPure, 10), $fineness, 12);
    }

    /**
     * Guard that a value is a numeric string before feeding it to BCMath.
     */
    protected function assertNumeric(string $value, string $field): void
    {
        if (! is_numeric($value)) {
            throw new InvalidArgumentException("El campo '{$field}' debe ser numérico.");
        }
    }
}
