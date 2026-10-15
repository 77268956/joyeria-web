<?php

namespace App\Enums;

enum StockCountStatus: string
{
    case Open = 'open';
    case Finished = 'finished';
    case Applied = 'applied';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierto',
            self::Finished => 'Finalizado',
            self::Applied => 'Aplicado',
        };
    }
}
