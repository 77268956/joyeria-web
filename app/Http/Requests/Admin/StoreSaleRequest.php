<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('sales.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
            'supervisor_pin' => ['nullable', 'string', 'size:4'],
            'discount_authorization_id' => ['nullable', 'integer', 'exists:discount_authorizations,id'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.method' => ['required', Rule::enum(PaymentMethod::class)],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.received' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'customer_id' => 'cliente',
            'items' => 'piezas',
            'items.*.product_id' => 'pieza',
            'items.*.discount' => 'descuento de línea',
            'discount_total' => 'descuento global',
            'discount_reason' => 'motivo del descuento',
            'supervisor_pin' => 'PIN de supervisor',
            'discount_authorization_id' => 'autorización de descuento',
            'payments' => 'pagos',
            'payments.*.method' => 'método de pago',
            'payments.*.amount' => 'monto del pago',
            'payments.*.received' => 'efectivo recibido',
            'payments.*.reference' => 'referencia del pago',
        ];
    }
}
