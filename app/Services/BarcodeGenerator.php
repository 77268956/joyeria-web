<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Minimal Code128 (code set B) barcode renderer that outputs inline SVG.
 *
 * The project intentionally avoids external barcode libraries, so this
 * self-contained encoder covers the labels printed by the inventory module.
 */
class BarcodeGenerator
{
    /**
     * Standard Code128 element width patterns, indexed by symbol value 0-106.
     *
     * @var array<int, string>
     */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_CODE_B = 104;

    private const STOP = 106;

    /**
     * Render the given value as an inline SVG Code128B barcode.
     */
    public function code128(string $data, int $moduleWidth = 2, int $barHeight = 70, int $quietZone = 10): string
    {
        $sequence = $this->encode($data);

        $widths = '';
        foreach ($sequence as $symbol) {
            $widths .= self::PATTERNS[$symbol];
        }

        $modules = 0;
        foreach (str_split($widths) as $digit) {
            $modules += (int) $digit;
        }

        $width = ($modules * $moduleWidth) + ($quietZone * 2);
        $height = $barHeight;

        $svg = sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" role="img" aria-label="%s">',
            $width,
            $height,
            $width,
            $height,
            htmlspecialchars($data, ENT_QUOTES)
        );

        $x = $quietZone;
        $isBar = true;

        foreach (str_split($widths) as $digit) {
            $barWidth = (int) $digit * $moduleWidth;

            if ($isBar) {
                $svg .= sprintf('<rect x="%d" y="0" width="%d" height="%d" fill="#000000"/>', $x, $barWidth, $height);
            }

            $x += $barWidth;
            $isBar = ! $isBar;
        }

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Build the full symbol sequence (start, data, checksum, stop) for code set B.
     *
     * @return array<int, int>
     */
    protected function encode(string $data): array
    {
        $symbols = [];

        for ($i = 0, $length = strlen($data); $i < $length; $i++) {
            $ordinal = ord($data[$i]);

            if ($ordinal < 32 || $ordinal > 126) {
                throw new InvalidArgumentException('El código de barras Code128B solo admite caracteres ASCII imprimibles (32-126).');
            }

            $symbols[] = $ordinal - 32;
        }

        $checksum = self::START_CODE_B;

        foreach ($symbols as $position => $value) {
            $checksum += $value * ($position + 1);
        }

        return array_merge([self::START_CODE_B], $symbols, [$checksum % 103, self::STOP]);
    }
}
