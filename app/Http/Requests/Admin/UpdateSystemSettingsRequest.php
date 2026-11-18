<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'system_name' => ['required', 'string', 'max:100'],
            'purchase_approval_limit' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'max_discount_percent' => ['required', 'numeric', 'between:0,100'],
            'layaway_max_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'low_stock_alert_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'cash_difference_alert_limit' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'ticket_footer_text' => ['nullable', 'string', 'max:2000'],
            'default_layaway_terms' => ['nullable', 'string', 'max:2000'],
            'default_purchase_disclaimer' => ['nullable', 'string', 'max:2000'],
            'whatsapp_number' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
        ];
    }

    /**
     * Normalize the system name before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('system_name')) {
            $this->merge([
                'system_name' => trim((string) $this->input('system_name')),
            ]);
        }
    }

    /**
     * Custom attribute names for validation.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'system_name' => 'nombre del sistema',
            'purchase_approval_limit' => 'límite de aprobación de compras',
            'max_discount_percent' => 'descuento máximo',
            'layaway_max_days' => 'días máximos de apartado',
            'low_stock_alert_threshold' => 'umbral de stock bajo',
            'cash_difference_alert_limit' => 'límite de diferencia de caja',
            'ticket_footer_text' => 'texto del pie de ticket',
            'default_layaway_terms' => 'términos de apartado',
            'default_purchase_disclaimer' => 'aviso legal de compra',
            'whatsapp_number' => 'número de WhatsApp',
        ];
    }
}
