<?php

namespace App\Http\Requests\Admin;

use App\Enums\PayoutMethod;
use App\Enums\PurchaseDestination;
use App\Enums\WeightSource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMetalPurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('purchases.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'payout_method' => ['required', Rule::enum(PayoutMethod::class)],
            'notes' => ['nullable', 'string', 'max:2000'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.metal_id' => ['required', 'integer', 'exists:metals,id'],
            'items.*.purity_id' => ['required', 'integer', 'exists:metal_purities,id'],
            'items.*.description' => ['required', 'string', 'max:150'],
            'items.*.gross_weight_g' => ['required', 'numeric', 'gt:0'],
            'items.*.net_weight_g' => ['required', 'numeric', 'gt:0'],
            'items.*.weight_source' => ['required', Rule::enum(WeightSource::class)],
            'items.*.destination' => ['required', Rule::enum(PurchaseDestination::class)],
            'items.*.photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
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
            'payout_method' => 'método de pago',
            'items' => 'piezas',
            'items.*.metal_id' => 'metal',
            'items.*.purity_id' => 'pureza',
            'items.*.description' => 'descripción de la pieza',
            'items.*.gross_weight_g' => 'peso bruto',
            'items.*.net_weight_g' => 'peso neto',
            'items.*.weight_source' => 'origen del peso',
            'items.*.destination' => 'destino',
            'items.*.photo' => 'foto de la pieza',
        ];
    }
}
