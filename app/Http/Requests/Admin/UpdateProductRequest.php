<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductOrigin;
use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateProductRequest extends FormRequest
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
        $product = $this->route('product');
        $storeId = $this->user()?->store_id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'metal_id' => ['required', 'exists:metals,id'],
            'purity_id' => [
                'nullable',
                Rule::exists('metal_purities', 'id')->where('metal_id', $this->input('metal_id')),
            ],
            'weight_g' => ['required', 'numeric', 'gt:0'],
            'cost' => ['required', 'numeric', 'gte:0'],
            'price' => ['required', 'numeric', 'gte:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'status' => ['nullable', new Enum(ProductStatus::class)],
            'origin' => ['nullable', new Enum(ProductOrigin::class)],
            'sku' => ['required', 'string', 'max:40', Rule::unique('products', 'sku')->where('store_id', $storeId)->ignore($product?->id)],
            'barcode' => ['required', 'string', 'max:60', Rule::unique('products', 'barcode')->where('store_id', $storeId)->ignore($product?->id)],
            'is_featured' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
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
            'name' => 'nombre de la pieza',
            'category_id' => 'categoría',
            'metal_id' => 'metal',
            'purity_id' => 'pureza',
            'weight_g' => 'peso en gramos',
            'cost' => 'costo',
            'price' => 'precio de venta',
            'location_id' => 'ubicación',
            'photos.*' => 'fotografía',
        ];
    }
}
