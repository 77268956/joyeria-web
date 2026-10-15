<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CloseCashSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('cash.close') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'counted_cash' => ['required', 'numeric', 'min:0'],
            'counted_card' => ['required', 'numeric', 'min:0'],
            'counted_transfer' => ['required', 'numeric', 'min:0'],
            'close_note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom attribute names for validation.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'counted_cash' => 'efectivo contado',
            'counted_card' => 'tarjeta contada',
            'counted_transfer' => 'transferencia contada',
            'close_note' => 'nota de cierre',
        ];
    }
}
