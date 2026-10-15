<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An audited correction applied to a metal purchase.
 *
 * Adjustments are immutable: they record the values before and after the
 * correction and never edit the original purchase line.
 * See docs/05-esquema-bd.md §9.
 */
class PurchaseAdjustment extends Model
{
    /**
     * The table only records the creation instant.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metal_purchase_id',
        'user_id',
        'reason',
        'old_values',
        'new_values',
        'amount_difference',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'amount_difference' => 'decimal:2',
        ];
    }

    /**
     * The purchase that was adjusted.
     */
    public function metalPurchase(): BelongsTo
    {
        return $this->belongsTo(MetalPurchase::class);
    }

    /**
     * The user who applied the adjustment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
