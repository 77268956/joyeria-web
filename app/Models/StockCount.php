<?php

namespace App\Models;

use App\Enums\StockCountStatus;
use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A physical inventory audit that reconciles expected stock with what is
 * actually found, generating adjustment movements for the differences.
 * See docs/05-esquema-bd.md §6 and PRD §6.3.
 */
class StockCount extends Model
{
    use BelongsToStore, HasFactory;

    /**
     * The table has no created_at / updated_at columns.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'status',
        'started_by',
        'started_at',
        'finished_at',
        'finished_by',
        'applied_at',
        'applied_by',
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
            'status' => StockCountStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'applied_at' => 'datetime',
        ];
    }

    /**
     * The pieces being counted.
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockCountItem::class);
    }

    /**
     * The user who opened the count.
     */
    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    /**
     * The user who finished the count.
     */
    public function finishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finished_by');
    }

    /**
     * The user who applied the count.
     */
    public function appliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applied_by');
    }

    /**
     * Whether the count is still being filled in.
     */
    public function isOpen(): bool
    {
        return $this->status === StockCountStatus::Open;
    }

    /**
     * Whether the count is finished and ready to be applied.
     */
    public function isFinished(): bool
    {
        return $this->status === StockCountStatus::Finished;
    }

    /**
     * Whether the count has already adjusted the inventory.
     */
    public function isApplied(): bool
    {
        return $this->status === StockCountStatus::Applied;
    }
}
