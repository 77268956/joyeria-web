<?php

namespace App\Models;

use App\Enums\MetalPurchaseStatus;
use App\Enums\PayoutMethod;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A purchase of precious metal from an identified customer.
 *
 * Purchases are immutable: corrections are recorded as `purchase_adjustments`.
 * See docs/05-esquema-bd.md §9 and PRD §6.2.
 */
class MetalPurchase extends Model
{
    use BelongsToStore, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'number',
        'customer_id',
        'user_id',
        'cash_session_id',
        'status',
        'total_amount',
        'payout_method',
        'approved_by',
        'approved_at',
        'approval_note',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MetalPurchaseStatus::class,
            'payout_method' => PayoutMethod::class,
            'total_amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * The customer who sold the metal.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The user who registered the purchase.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The shift the purchase was registered against.
     */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /**
     * The user who approved or rejected the purchase.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * The pieces bought in this purchase.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MetalPurchaseItem::class);
    }

    /**
     * The immutable adjustments recorded against this purchase.
     */
    public function adjustments(): HasMany
    {
        return $this->hasMany(PurchaseAdjustment::class);
    }

    /**
     * Scope the query to purchases awaiting approval.
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', MetalPurchaseStatus::PendingApproval->value);
    }

    /**
     * Whether the purchase is waiting for a manager decision.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === MetalPurchaseStatus::PendingApproval;
    }

    /**
     * Whether the purchase has been settled and paid.
     */
    public function isPaid(): bool
    {
        return $this->status === MetalPurchaseStatus::Paid;
    }
}
