<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment applied to a sale. Rows are immutable once created.
 */
class Payment extends Model
{
    /**
     * The ledger only records the creation instant.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_id',
        'cash_session_id',
        'method',
        'amount',
        'received',
        'reference',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'received' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The sale this payment settles.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * The shift during which the payment was collected.
     */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }
}
