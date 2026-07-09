<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalculateRequest extends FormRequest
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
            'amount' => ['required', 'numeric', 'min:1', 'max:500000'],
            'platform' => ['required', 'string', 'in:upwork,fiverr,toptal'],
            'processor' => ['required', 'string', 'in:payoneer,wise,direct'],
            'currency' => ['required', 'string', 'in:USD,GBP,EUR'],
        ];
    }
}
