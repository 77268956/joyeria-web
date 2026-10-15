<?php

namespace App\Enums;

enum ProductOrigin: string
{
    case New = 'new';
    case UsedPurchase = 'used_purchase';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::New => 'Nueva',
            self::UsedPurchase => 'Comprada (ocasión)',
        };
    }
}
