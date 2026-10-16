<?php

namespace App\Models;

use App\Enums\DiscountAuthorizationStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountAuthorization extends Model
{
    use BelongsToStore;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'requested_by',
        'authorized_by',
        'cash_session_id',
        'status',
        'sale_snapshot',
        'subtotal',
        'discount_amount',
        'discount_percent',
        'reason',
        'rejection_reason',
        'expires_at',
        'resolved_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_snapshot' => 'array',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'status' => DiscountAuthorizationStatus::class,
            'expires_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /**
     * Whether this authorization can still be approved or rejected.
     */
    public function isPending(): bool
    {
        return $this->status === DiscountAuthorizationStatus::Pending
            && $this->expires_at->isFuture();
    }

    /**
     * Whether this authorization has been approved and is still usable.
     */
    public function isUsable(): bool
    {
        return $this->status === DiscountAuthorizationStatus::Approved
            && $this->expires_at->isFuture();
    }
}
