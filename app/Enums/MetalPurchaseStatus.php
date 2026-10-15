<?php

namespace App\Enums;

enum MetalPurchaseStatus: string
{
    case PendingApproval = 'pending_approval';
    case Paid = 'paid';
    case Rejected = 'rejected';
    case Adjusted = 'adjusted';

    /**
     * Human readable label shown in the admin panel (Spanish).
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingApproval => 'Pendiente de aprobación',
            self::Paid => 'Pagada',
            self::Rejected => 'Rechazada',
            self::Adjusted => 'Ajustada',
        };
    }
}
