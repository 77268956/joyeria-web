<?php

namespace App\Models;

use App\Enums\SaleStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use BelongsToStore, HasFactory;

    protected $fillable = [
        'store_id',
        'number',
        'customer_id',
        'user_id',
        'cash_session_id',
        'subtotal',
        'discount_total',
        'total',
        'status',
        'voided_at',
        'voided_by',
        'void_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'total' => 'decimal:2',
            'status' => SaleStatus::class,
            'voided_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The shift during which the sale was registered.
     */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /**
     * The user who voided the sale.
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * The payments that settled this sale.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope the query to sales that are still valid (not voided).
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', SaleStatus::Completed->value);
    }

    /**
     * Whether the sale has been voided.
     */
    public function isVoided(): bool
    {
        return $this->status === SaleStatus::Voided;
    }
}
