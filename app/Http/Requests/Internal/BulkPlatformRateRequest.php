<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class BulkPlatformRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rates' => ['required', 'array', 'min:1'],
            'rates.*.platform' => ['required', 'string', 'max:50'],
            'rates.*.fee_type' => ['required', 'string', 'max:50'],
            'rates.*.fee_value' => ['required', 'numeric', 'min:0'],
            'rates.*.currency' => ['nullable', 'string', 'size:3'],
            'rates.*.effective_date' => ['nullable', 'date'],
            'effective_date' => ['nullable', 'date'],
        ];
    }
}
