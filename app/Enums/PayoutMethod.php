<?php

namespace App\Enums;

enum PayoutMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Efectivo',
            self::Transfer => 'Transferencia',
        };
    }
}
