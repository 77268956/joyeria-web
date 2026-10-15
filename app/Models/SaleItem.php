<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An immutable line of a sale, with a snapshot of the piece at sale time.
 */
class SaleItem extends Model
{
    /**
     * Line items carry no timestamps of their own; the sale does.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'name_snapshot',
        'metal_snapshot',
        'purity_snapshot',
        'weight_g',
        'unit_price',
        'discount',
        'line_total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_g' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    /**
     * The sale this line belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * The piece that was sold (kept for traceability).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
}
