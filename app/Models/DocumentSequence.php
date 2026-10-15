<?php

namespace App\Models;

use App\Traits\BelongsToStore;
use Illuminate\Database\Eloquent\Model;

/**
 * Strict consecutive numbering per store and document type.
 *
 * A row is locked with `lockForUpdate` inside a transaction before it is
 * consumed, guaranteeing collision-free numbers. See docs/05-esquema-bd.md.
 */
class DocumentSequence extends Model
{
    use BelongsToStore;

    /**
     * Sequences carry no timestamps.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'type',
        'prefix',
        'next_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'next_number' => 'integer',
        ];
    }
}
