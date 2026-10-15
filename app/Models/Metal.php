<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Metal extends Model
{
    use HasFactory;

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
        'code',
        'name',
        'is_precious',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_precious' => 'boolean',
        ];
    }

    /**
     * Get the purities associated with this metal.
     */
    public function purities(): HasMany
    {
        return $this->hasMany(MetalPurity::class);
    }

    /**
     * Get the products made of this metal.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the price snapshots of this metal.
     */
    public function prices(): HasMany
    {
        return $this->hasMany(MetalPrice::class);
    }

    /**
     * Get the purchase discount settings of this metal.
     */
    public function purchaseSettings(): HasMany
    {
        return $this->hasMany(PurchaseSetting::class);
    }
}
