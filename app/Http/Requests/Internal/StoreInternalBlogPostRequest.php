<?php

namespace App\Http\Requests\Internal;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternalBlogPostRequest extends FormRequest
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
            'topic_key' => ['required', 'string', 'max:64'],
            'excerpt' => ['required', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:50000'],
            'hero_image_url' => ['nullable', 'url', 'max:500'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'source_url' => ['nullable', 'url', 'max:500'],
            'source_name' => ['nullable', 'string', 'max:50'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:50'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
