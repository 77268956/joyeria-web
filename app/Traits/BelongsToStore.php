<?php

namespace App\Traits;

use App\Models\Scopes\StoreScope;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait BelongsToStore
{
    /**
     * Boot the trait to add the global store scope and automatically fill store_id on creation.
     */
    protected static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function (Model $model): void {
            if (empty($model->store_id) && Auth::check() && Auth::user()?->store_id) {
                $model->store_id = Auth::user()->store_id;
            }
        });
    }

    /**
     * Get the store that this model belongs to.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope a query to include records across all stores, bypassing the global store scope.
     */
    public function scopeAllStores(Builder $query): Builder
    {
        return $query->withoutGlobalScope(StoreScope::class);
    }
}
