<?php

namespace App\Enums;

enum CashSessionStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierta',
            self::Closed => 'Cerrada',
        };
    }
}
