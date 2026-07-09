<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ToolFilterRequest extends FormRequest
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
            'sort' => ['nullable', 'string', 'in:stars,updated,savings'],
            'docker' => ['nullable', 'boolean'],
            'laravel' => ['nullable', 'boolean'],
            'php_version' => ['nullable', 'string', 'max:10'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'docker' => $this->boolean('docker'),
            'laravel' => $this->boolean('laravel'),
        ]);
    }
}
