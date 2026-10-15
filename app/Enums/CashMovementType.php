<?php

namespace App\Enums;

enum CashMovementType: string
{
    case In = 'in';
    case Out = 'out';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::In => 'Ingreso',
            self::Out => 'Retiro',
        };
    }

    /**
     * Signed multiplier applied to the movement amount for totals.
     */
    public function sign(): int
    {
        return $this === self::In ? 1 : -1;
    }
}
