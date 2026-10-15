<?php

namespace Tests\Unit;

use App\Services\BarcodeGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BarcodeGeneratorTest extends TestCase
{
    public function test_it_renders_a_code128_svg_with_bars(): void
    {
        $svg = (new BarcodeGenerator)->code128('123456789012');

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertStringEndsWith('</svg>', $svg);
        $this->assertStringContainsString('<rect', $svg);
        $this->assertStringContainsString('123456789012', $svg);
    }

    public function test_it_accepts_printable_ascii_sku_values(): void
    {
        $svg = (new BarcodeGenerator)->code128('PRD-001-00042');

        $this->assertStringContainsString('<rect', $svg);
    }

    public function test_it_rejects_non_printable_ascii_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new BarcodeGenerator)->code128("bad\x01value");
    }
}
