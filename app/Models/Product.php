<?php

namespace App\Models;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use BelongsToStore, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'weight_g' => 'decimal:3',
            'cost' => 'decimal:2',
            'price' => 'decimal:2',
            'status' => ProductStatus::class,
            'origin' => ProductOrigin::class,
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    /**
     * The category of this piece.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The metal of this piece.
     */
    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    /**
     * The purity of this piece.
     */
    public function purity(): BelongsTo
    {
        return $this->belongsTo(MetalPurity::class, 'purity_id');
    }

    /**
     * The physical location of this piece.
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * All photos of this piece, in display order.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ProductPhoto::class)->orderBy('sort_order');
    }

    /**
     * The primary photo of this piece.
     */
    public function primaryPhoto(): HasOne
    {
        return $this->hasOne(ProductPhoto::class)->where('is_primary', true);
    }

    /**
     * The immutable movements that explain this piece's stock.
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest('id');
    }

    /**
     * Scope the query to searchable pieces by name, SKU or barcode.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $like = '%'.$term.'%';

        return $query->where(function (Builder $query) use ($like) {
            $query->where('name', 'like', $like)
                ->orWhere('sku', 'like', $like)
                ->orWhere('barcode', 'like', $like);
        });
    }

    /**
     * Scope the query to pieces that are physically in stock.
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->whereIn('status', [ProductStatus::Available->value, ProductStatus::Reserved->value]);
    }

    /**
     * Scope the query to pieces that may appear on the public website.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_public', true)
            ->where('status', ProductStatus::Available->value);
    }

    /**
     * Scope the query to pieces highlighted on the public website.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Generate a unique SKU for the given store.
     */
    public static function generateSku(int $storeId): string
    {
        $next = static::nextSequenceFor($storeId, 'sku');

        do {
            $sku = sprintf('PRD-%03d-%05d', $storeId, $next);
            $next++;
        } while (static::withoutGlobalScopes()->where('store_id', $storeId)->where('sku', $sku)->exists());

        return $sku;
    }

    /**
     * Generate a unique numeric barcode (Code128 encodable) for the given store.
     */
    public static function generateBarcode(int $storeId): string
    {
        do {
            $barcode = (string) random_int(100000000000, 999999999999);
        } while (static::withoutGlobalScopes()->where('store_id', $storeId)->where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * Guess the next numeric suffix from the last SKU already created.
     */
    protected static function nextSequenceFor(int $storeId, string $column): int
    {
        $last = static::withoutGlobalScopes()
            ->where('store_id', $storeId)
            ->orderByDesc('id')
            ->value($column);

        if ($last && preg_match('/(\d+)$/', (string) $last, $matches)) {
            return (int) $matches[1] + 1;
        }

        return 1;
    }
}
