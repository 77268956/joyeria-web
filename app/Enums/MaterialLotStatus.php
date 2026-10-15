<?php

namespace App\Enums;

enum MaterialLotStatus: string
{
    case Open = 'open';
    case SentToMelting = 'sent_to_melting';
    case Closed = 'closed';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::Open => 'Abierto',
            self::SentToMelting => 'Enviado a fundición',
            self::Closed => 'Cerrado',
        };
    }
}
