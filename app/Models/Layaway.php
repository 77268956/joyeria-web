<?php

namespace App\Models;

use App\Enums\LayawayStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A reserved piece paid in instalments until it is delivered to the customer.
 *
 * The balance is always kept equal to `total_price - paid_total`; every
 * payment is stored as an immutable `layaway_payments` row.
 * See docs/05-esquema-bd.md §8 and PRD §6.1.
 */
class Layaway extends Model
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
        'product_id',
        'user_id',
        'total_price',
        'paid_total',
        'balance',
        'due_date',
        'status',
        'cancel_reason',
        'completed_sale_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'paid_total' => 'decimal:2',
            'balance' => 'decimal:2',
            'due_date' => 'date',
            'status' => LayawayStatus::class,
        ];
    }

    /**
     * The customer who is paying the piece.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * The reserved piece.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The user who opened the layaway.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The sale generated once the layaway was delivered.
     */
    public function completedSale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'completed_sale_id');
    }

    /**
     * The instalments collected for this layaway.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(LayawayPayment::class);
    }

    /**
     * Scope the query to layaways that are still pending delivery.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', LayawayStatus::Active->value);
    }

    /**
     * Whether the layaway is still pending delivery.
     */
    public function isActive(): bool
    {
        return $this->status === LayawayStatus::Active;
    }

    /**
     * Whether the layaway has been fully paid and delivered.
     */
    public function isCompleted(): bool
    {
        return $this->status === LayawayStatus::Completed;
    }

    /**
     * Whether the layaway was cancelled and the piece released.
     */
    public function isCancelled(): bool
    {
        return $this->status === LayawayStatus::Cancelled;
    }

    /**
     * Whether the outstanding balance has reached zero.
     */
    public function isFullyPaid(): bool
    {
        return bccomp((string) $this->balance, '0', 2) === 0;
    }
}
