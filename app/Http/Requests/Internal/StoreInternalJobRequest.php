<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalJobRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'salary_min' => ['nullable', 'integer', 'min:0'],
            'salary_max' => ['nullable', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'stack' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:100'],
            'remote_type' => ['required', 'string', 'max:50'],
            'source_url' => ['required', 'url', 'max:500'],
            'posted_at' => ['nullable', 'date'],
        ];
    }
}
