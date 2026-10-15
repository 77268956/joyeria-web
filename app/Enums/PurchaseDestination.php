<?php

namespace App\Enums;

enum PurchaseDestination: string
{
    case UsedInventory = 'used_inventory';
    case Melting = 'melting';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::UsedInventory => 'Inventario de ocasión',
            self::Melting => 'Materia prima (fundición)',
        };
    }
}
