<?php

namespace App\Models;

use App\Enums\CashSessionStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    use BelongsToStore, HasFactory;

    /**
     * The shift tracks its own open/close timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'user_id',
        'status',
        'opened_at',
        'opening_amount',
        'closed_at',
        'closed_by',
        'expected_cash',
        'expected_card',
        'expected_transfer',
        'counted_cash',
        'counted_card',
        'counted_transfer',
        'difference_total',
        'close_note',
        'supervisor_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => CashSessionStatus::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_amount' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'expected_card' => 'decimal:2',
            'expected_transfer' => 'decimal:2',
            'counted_cash' => 'decimal:2',
            'counted_card' => 'decimal:2',
            'counted_transfer' => 'decimal:2',
            'difference_total' => 'decimal:2',
        ];
    }

    /**
     * The cashier who owns this shift.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who closed the shift.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * The supervisor who authorized the shift close when there was a discrepancy.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * The justified cash injections and withdrawals of this shift.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * The sales registered during this shift.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * The payments collected during this shift.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope the query to shifts that are still open.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', CashSessionStatus::Open->value);
    }

    /**
     * Whether the shift is still open.
     */
    public function isOpen(): bool
    {
        return $this->status === CashSessionStatus::Open;
    }
}
