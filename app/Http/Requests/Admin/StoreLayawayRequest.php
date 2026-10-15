<?php

namespace App\Http\Requests\Admin;

use App\Enums\PaymentMethod;
use App\Models\SystemSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreLayawayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('layaways.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'due_date' => [
                'required',
                'date',
                'after_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $maxDays = (int) SystemSetting::firstOrCreate(['store_id' => $this->user()->store_id])->layaway_max_days;
                    $limit = now()->startOfDay()->addDays($maxDays);

                    if (Carbon::parse($value)->startOfDay()->greaterThan($limit)) {
                        $fail("La fecha límite no puede exceder los {$maxDays} días permitidos.");
                    }
                },
            ],
            'initial_amount' => ['required', 'numeric', 'min:0'],
            'initial_method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:80'],
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
            'product_id' => 'pieza',
            'customer_id' => 'cliente',
            'due_date' => 'fecha límite',
            'initial_amount' => 'abono inicial',
            'initial_method' => 'método del abono inicial',
            'reference' => 'referencia',
        ];
    }
}
