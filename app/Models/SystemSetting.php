<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SystemSetting extends Model
{
    use HasFactory;

    public const CREATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'store_id',
        'system_name',
        'logo_light_path',
        'logo_dark_path',
        'favicon_path',
        'primary_color',
        'background_color',
        'accent_gold_color',
        'accent_light_gold',
        'purchase_approval_limit',
        'max_discount_percent',
        'layaway_max_days',
        'ticket_footer_text',
        'low_stock_alert_threshold',
        'cash_difference_alert_limit',
        'default_layaway_terms',
        'default_purchase_disclaimer',
        'metal_api_provider',
        'whatsapp_number',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_approval_limit' => 'decimal:2',
            'max_discount_percent' => 'decimal:2',
            'cash_difference_alert_limit' => 'decimal:2',
            'layaway_max_days' => 'integer',
            'low_stock_alert_threshold' => 'integer',
        ];
    }

    /**
     * Get the store that owns these settings.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
