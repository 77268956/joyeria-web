<?php

namespace Tests\Unit;

use App\Services\PricingCalculator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PricingCalculatorTest extends TestCase
{
    protected PricingCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new PricingCalculator;
    }

    public function test_it_converts_a_spot_ounce_price_into_a_price_per_gram(): void
    {
        $this->assertSame('1.0000', $this->calculator->pricePerGram('31.1034768'));
        $this->assertSame('5.0000', $this->calculator->pricePerGram('155.5173840'));
    }

    public function test_it_applies_the_exchange_rate_before_converting_per_gram(): void
    {
        $this->assertSame('2.0000', $this->calculator->pricePerGram('31.1034768', '2'));
    }

    public function test_it_values_gold_by_karat_fineness(): void
    {
        $pricePerGram = '100.0000';

        $this->assertSame('100.00', $this->calculator->metalValue('1', $pricePerGram, $this->calculator->finenessFromKarat(24)));
        $this->assertSame('75.00', $this->calculator->metalValue('1', $pricePerGram, $this->calculator->finenessFromKarat(18)));
        $this->assertSame('58.33', $this->calculator->metalValue('1', $pricePerGram, $this->calculator->finenessFromKarat(14)));
        $this->assertSame('41.67', $this->calculator->metalValue('1', $pricePerGram, $this->calculator->finenessFromKarat(10)));
    }

    public function test_it_values_gold_14k_and_18k_pieces(): void
    {
        $this->assertSame('375.00', $this->calculator->metalValue('10', '50.0000', '0.7500'));
        $this->assertSame('291.65', $this->calculator->metalValue('10', '50.0000', '0.5833'));
    }

    public function test_it_values_silver_by_ley_fineness(): void
    {
        $this->assertSame('18.50', $this->calculator->metalValue('20', '1.0000', '0.9250'));
        $this->assertSame('19.00', $this->calculator->metalValue('20', '1.0000', '0.9500'));
    }

    public function test_it_applies_the_purchase_discount(): void
    {
        $this->assertSame('337.50', $this->calculator->purchaseAmount('10', '50.0000', '0.7500', '10'));
        $this->assertSame('17.02', $this->calculator->purchaseAmount('20', '1.0000', '0.9250', '8'));
        $this->assertSame('375.00', $this->calculator->purchaseAmount('10', '50.0000', '0.7500', '0'));
    }

    public function test_it_rounds_half_up_to_two_decimals(): void
    {
        $this->assertSame('2.68', $this->calculator->round('2.675', 2));
        $this->assertSame('2.67', $this->calculator->round('2.674', 2));
        $this->assertSame('1.01', $this->calculator->round('1.005', 2));
        $this->assertSame('10.00', $this->calculator->round('10', 2));
        $this->assertSame('-2.68', $this->calculator->round('-2.675', 2));
    }

    public function test_it_rejects_an_out_of_range_karat(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->finenessFromKarat(25);
    }

    public function test_it_rejects_non_numeric_input(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->calculator->metalValue('abc', '50.0000', '0.7500');
    }
}
