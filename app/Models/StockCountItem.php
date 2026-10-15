<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single piece within a physical inventory count.
 *
 * `expected` marks whether the system believed the piece was on hand;
 * `found` records what the physical count found (null until counted).
 */
class StockCountItem extends Model
{
    use HasFactory;

    /**
     * The count line only stores its current values.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'stock_count_id',
        'product_id',
        'expected',
        'found',
        'note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expected' => 'boolean',
            'found' => 'boolean',
        ];
    }

    /**
     * The count this item belongs to.
     */
    public function stockCount(): BelongsTo
    {
        return $this->belongsTo(StockCount::class);
    }

    /**
     * The piece being counted.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Whether the piece was expected but not found (a shortage).
     */
    public function isMissing(): bool
    {
        return $this->expected && $this->found === false;
    }

    /**
     * Whether the piece was found although the system did not expect it (a surplus).
     */
    public function isSurplus(): bool
    {
        return ! $this->expected && $this->found === true;
    }

    /**
     * Whether the piece is still pending to be counted.
     */
    public function isPending(): bool
    {
        return $this->found === null;
    }
}
