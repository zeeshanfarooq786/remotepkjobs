<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalarySnapshotRequest extends FormRequest
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
            'stack' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:100'],
            'avg_salary' => ['required', 'integer', 'min:0'],
            'sample_size' => ['required', 'integer', 'min:1'],
            'recorded_at' => ['nullable', 'date'],
        ];
    }
}
