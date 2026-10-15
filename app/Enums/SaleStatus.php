<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Voided = 'voided';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Completed => 'Completada',
            self::Voided => 'Anulada',
        };
    }
}
