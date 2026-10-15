<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaterialLotRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('inventory.adjust') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $storeId = $this->user()?->store_id;

        return [
            'metal_id' => ['required', 'exists:metals,id'],
            'purity_id' => [
                'required',
                Rule::exists('metal_purities', 'id')->where('metal_id', $this->input('metal_id')),
            ],
            'initial_weight_g' => ['required', 'numeric', 'gt:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'code' => ['nullable', 'string', 'max:40', Rule::unique('material_lots', 'code')->where('store_id', $storeId)],
            'notes' => ['nullable', 'string'],
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
            'metal_id' => 'metal',
            'purity_id' => 'pureza',
            'initial_weight_g' => 'peso inicial en gramos',
            'location_id' => 'ubicación',
        ];
    }
}
