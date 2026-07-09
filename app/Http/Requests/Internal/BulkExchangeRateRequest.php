<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class BulkExchangeRateRequest extends FormRequest
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
            'rates.*.from_currency' => ['required', 'string', 'size:3'],
            'rates.*.to_currency' => ['required', 'string', 'size:3'],
            'rates.*.rate' => ['required', 'numeric', 'min:0'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
