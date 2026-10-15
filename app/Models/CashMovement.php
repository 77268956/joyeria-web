<?php

namespace App\Models;

use App\Enums\CashMovementType;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A justified cash injection or withdrawal inside a shift.
 *
 * Rows are only inserted, never updated nor deleted.
 */
class CashMovement extends Model
{
    use BelongsToStore;

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
        'store_id',
        'cash_session_id',
        'type',
        'amount',
        'reason',
        'user_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CashMovementType::class,
            'amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The shift this movement belongs to.
     */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /**
     * The user who registered the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
