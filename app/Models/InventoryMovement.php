<?php

namespace App\Models;

use App\Enums\InventoryMovementType;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Immutable inventory ledger — the single source of truth for stock.
 *
 * Rows are only inserted, never updated nor deleted. See the database
 * schema (docs/05-esquema-bd.md) for the full contract.
 */
class InventoryMovement extends Model
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
        'product_id',
        'material_lot_id',
        'type',
        'quantity_delta',
        'weight_delta_g',
        'reference_type',
        'reference_id',
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
            'type' => InventoryMovementType::class,
            'quantity_delta' => 'integer',
            'weight_delta_g' => 'decimal:3',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The piece this movement belongs to (when piece-level).
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * The material lot this movement belongs to (when lot-level).
     */
    public function materialLot(): BelongsTo
    {
        return $this->belongsTo(MaterialLot::class);
    }

    /**
     * The user who registered the movement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The originating document (sale, purchase, stock count).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
