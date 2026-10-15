<?php

namespace App\Enums;

enum InventoryMovementType: string
{
    case Entry = 'entry';
    case PurchaseEntry = 'purchase_entry';
    case Sale = 'sale';
    case SaleVoid = 'sale_void';
    case LayawayReserve = 'layaway_reserve';
    case LayawayRelease = 'layaway_release';
    case Adjustment = 'adjustment';
    case ToMelting = 'to_melting';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Entry => 'Entrada',
            self::PurchaseEntry => 'Entrada por compra',
            self::Sale => 'Venta',
            self::SaleVoid => 'Anulación de venta',
            self::LayawayReserve => 'Apartado',
            self::LayawayRelease => 'Liberación de apartado',
            self::Adjustment => 'Ajuste',
            self::ToMelting => 'Envío a fundición',
        };
    }
}
