<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Store margin applied when buying precious metal from a customer.
 *
 * One row per (store, metal, purity); a null purity_id is the metal's default
 * discount. See docs/05-esquema-bd.md §7 and PRD §7.1/§7.2.
 */
class PurchaseSetting extends Model
{
    use BelongsToStore;

    /**
     * The table only records the last update instant.
     */
    public const CREATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'metal_id',
        'purity_id',
        'discount_percent',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_percent' => 'decimal:2',
        ];
    }

    /**
     * The metal this setting belongs to.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * The purity this setting applies to (null = metal default).
     */
    public function purity(): BelongsTo
    {
        return $this->belongsTo(MetalPurity::class, 'purity_id');
    }

    /**
     * The user who last updated this setting.
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
