<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Card = 'card';
    case Transfer = 'transfer';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Card => 'Tarjeta',
            self::Transfer => 'Transferencia',
        };
    }
}
