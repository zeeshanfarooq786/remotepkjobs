<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAlternativeRequest extends FormRequest
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
            'paid_tool' => ['required', 'string', 'max:100'],
            'github_stars' => ['required', 'integer', 'min:0'],
            'github_forks' => ['nullable', 'integer', 'min:0'],
            'last_commit' => ['nullable', 'date'],
            'open_issues' => ['nullable', 'integer', 'min:0'],
            'language' => ['nullable', 'string', 'max:50'],
        ];
    }
}
