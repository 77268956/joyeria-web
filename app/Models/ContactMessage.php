<?php

namespace App\Models;

use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A message submitted through the public contact form.
 *
 * The table only records the creation instant and is not scoped by store
 * traits; queries for the panel should filter by `store_id` explicitly.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    /**
     * Lifecycle status of a message.
     */
    public const STATUS_NEW = 'new';

    public const STATUS_READ = 'read';

    public const STATUS_ANSWERED = 'answered';

    /**
     * The table has no `updated_at` column.
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'name',
        'email',
        'phone',
        'message',
        'status',
        'ip',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * The store that received this message.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
