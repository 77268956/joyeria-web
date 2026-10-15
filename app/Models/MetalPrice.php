<?php

namespace App\Models;

use App\Enums\MetalPriceSource;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable metal price snapshot.
 *
 * The current price of a metal is its most recent row. Rows are never edited:
 * a change inserts a new row. See docs/05-esquema-bd.md §7.
 */
class MetalPrice extends Model
{
    use BelongsToStore;

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
        'store_id',
        'metal_id',
        'price_per_gram',
        'spot_per_ounce',
        'exchange_rate',
        'source',
        'provider',
        'price_date',
        'set_by',
        'reason',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_per_gram' => 'decimal:4',
            'spot_per_ounce' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'source' => MetalPriceSource::class,
            'price_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The metal this price belongs to.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * The user who fixed this price manually, if any.
     */
    public function setBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
