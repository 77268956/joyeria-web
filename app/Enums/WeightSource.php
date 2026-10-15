<?php

namespace App\Enums;

enum WeightSource: string
{
    case Scale = 'scale';
    case Manual = 'manual';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Scale => 'Balanza',
            self::Manual => 'Manual',
        };
    }
}
