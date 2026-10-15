<?php

namespace App\Models;

use App\Enums\MaterialLotStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialLot extends Model
{
    use BelongsToStore, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'code',
        'metal_id',
        'purity_id',
        'initial_weight_g',
        'current_weight_g',
        'location_id',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_weight_g' => 'decimal:3',
            'current_weight_g' => 'decimal:3',
            'status' => MaterialLotStatus::class,
        ];
    }

    /**
     * The metal of this lot.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * The purity of this lot.
     */
    public function purity(): BelongsTo
    {
        return $this->belongsTo(MetalPurity::class, 'purity_id');
    }

    /**
     * The physical location of this lot.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * The immutable movements that explain this lot's weight.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest('id');
    }

    /**
     * Whether the lot can still receive or release weight.
     */
    public function isOpen(): bool
    {
        return $this->status === MaterialLotStatus::Open;
    }

    /**
     * Generate a unique lot code for the given store.
     */
    public static function generateCode(int $storeId): string
    {
        $last = static::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->orderByDesc('id')
            ->value('code');

        $next = 1;
        if ($last && preg_match('/(\d+)$/', (string) $last, $matches)) {
            $next = (int) $matches[1] + 1;
        }

        do {
            $code = sprintf('LOT-%03d-%05d', $storeId, $next);
            $next++;
        } while (static::withoutGlobalScopes()->where('store_id', $storeId)->where('code', $code)->exists());

        return $code;
    }
}
