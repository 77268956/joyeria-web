<?php

namespace App\Enums;

enum MetalPriceSource: string
{
    case Api = 'api';
    case Manual = 'manual';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Api => 'Automático (API)',
            self::Manual => 'Manual',
        };
    }
}
