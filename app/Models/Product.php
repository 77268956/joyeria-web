<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'metal_id',
        'purity_id',
        'weight_g',
        'cost',
        'price',
        'location_id',
        'status',
        'origin',
        'is_featured',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'weight_g' => 'decimal:3',
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class);
    }
}
