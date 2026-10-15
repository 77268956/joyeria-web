<?php

namespace App\Enums;

enum DocumentType: string
{
    case Sale = 'sale';
    case Purchase = 'purchase';
    case Layaway = 'layaway';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Sale => 'Venta',
            self::Purchase => 'Compra',
            self::Layaway => 'Apartado',
        };
    }

    /**
     * Default numbering prefix for this document type.
     */
    public function prefix(): string
    {
        return match ($this) {
            self::Sale => 'VTA-',
            self::Purchase => 'COM-',
            self::Layaway => 'APT-',
        };
    }
}
