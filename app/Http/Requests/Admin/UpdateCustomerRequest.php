<?php

namespace App\Http\Requests\Admin;

use App\Models\Customer;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('customers.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $storeId = $this->user()?->store_id;
        $customer = $this->route('customer');

        return [
            'full_name' => ['required', 'string', 'max:150'],
            'document_type' => ['required', 'string', 'max:20'],
            'document_number' => [
                'required',
                'string',
                'max:40',
                Rule::unique('customers', 'document_number')
                    ->where('store_id', $storeId)
                    ->where('document_type', $this->input('document_type'))
                    ->ignore($customer instanceof Customer ? $customer->id : $customer),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'id_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'full_name' => 'nombre completo',
            'document_type' => 'tipo de documento',
            'document_number' => 'número de documento',
            'birth_date' => 'fecha de nacimiento',
            'id_photo' => 'foto de identificación',
        ];
    }
}
