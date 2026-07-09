<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobFilterRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'stack' => ['nullable', 'string', 'in:Laravel,Python,React,Node'],
            'country' => ['nullable', 'string', 'max:100'],
            'salary_min' => ['nullable', 'integer', 'min:0', 'max:500000'],
            'salary_max' => ['nullable', 'integer', 'min:0', 'max:500000'],
            'remote_type' => ['nullable', 'string', 'in:full,part,contract'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
