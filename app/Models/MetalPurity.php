<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetalPurity extends Model
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
        'metal_id',
        'code',
        'label',
        'fineness',
        'is_active',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fineness' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the metal that this purity belongs to.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * Get the products with this metal purity.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'purity_id');
    }
}
