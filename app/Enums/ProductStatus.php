<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Sold = 'sold';
    case Melting = 'melting';
    case Withdrawn = 'withdrawn';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Available => 'Disponible',
            self::Reserved => 'Apartado',
            self::Sold => 'Vendido',
            self::Melting => 'En fundición',
            self::Withdrawn => 'Retirado',
        };
    }

    /**
     * Whether the piece physically counts as stock on hand.
     */
    public function isInStock(): bool
    {
        return in_array($this, [self::Available, self::Reserved], true);
    }
}
