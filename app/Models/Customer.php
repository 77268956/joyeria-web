<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'full_name',
        'document_type',
        'document_number',
        'phone',
        'email',
        'address',
        'birth_date',
        'id_photo_path',
        'notes',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
