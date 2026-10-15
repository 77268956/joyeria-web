<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable instalment collected against a layaway.
 *
 * The shift that received the money is stored so the row participates in the
 * cash reconciliation of `CashSessionService`.
 */
class LayawayPayment extends Model
{
    use HasFactory;

    /**
     * The instalment only tracks the moment it was collected.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'layaway_id',
        'cash_session_id',
        'method',
        'amount',
        'reference',
        'user_id',
        'created_at',
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
            'created_at' => 'datetime',
        ];
    }

    /**
     * The layaway this instalment belongs to.
     */
    public function layaway(): BelongsTo
    {
        return $this->belongsTo(Layaway::class);
    }

    /**
     * The shift during which the instalment was collected.
     */
    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    /**
     * The user who collected the instalment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
