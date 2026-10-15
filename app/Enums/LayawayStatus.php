<?php

namespace App\Enums;

enum LayawayStatus: string
{
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
        };
    }
}
