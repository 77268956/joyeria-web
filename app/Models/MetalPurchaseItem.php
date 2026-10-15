<?php

namespace App\Models;

use App\Enums\PurchaseDestination;
use App\Enums\WeightSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single piece bought in a metal purchase, frozen with valuation snapshots.
 *
 * See docs/05-esquema-bd.md §9.
 */
class MetalPurchaseItem extends Model
{
    /**
     * The table does not have timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'metal_purchase_id',
        'metal_id',
        'purity_id',
        'description',
        'gross_weight_g',
        'net_weight_g',
        'weight_source',
        'fineness_snapshot',
        'price_per_gram_snapshot',
        'discount_percent_snapshot',
        'amount',
        'photo_path',
        'destination',
        'product_id',
        'material_lot_id',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_weight_g' => 'decimal:3',
            'net_weight_g' => 'decimal:3',
            'weight_source' => WeightSource::class,
            'fineness_snapshot' => 'decimal:4',
            'price_per_gram_snapshot' => 'decimal:4',
            'discount_percent_snapshot' => 'decimal:2',
            'amount' => 'decimal:2',
            'destination' => PurchaseDestination::class,
        ];
    }

    /**
     * The purchase this item belongs to.
     */
    public function metalPurchase(): BelongsTo
    {
        return $this->belongsTo(MetalPurchase::class);
    }

    /**
     * The metal of this piece.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * The purity used for the valuation.
     */
    public function purity(): BelongsTo
    {
        return $this->belongsTo(MetalPurity::class, 'purity_id');
    }

    /**
     * The piece created in the used inventory, if that was the destination.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The raw-material lot created for melting, if that was the destination.
     */
    public function materialLot(): BelongsTo
    {
        return $this->belongsTo(MaterialLot::class);
    }
}
