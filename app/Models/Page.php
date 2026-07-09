<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'meta_description',
        'content_json',
        'tool_type',
        'published_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'published_at' => 'datetime',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
    ];

    protected function content(): Attribute
    {
        return Attribute::get(function () {
            if (is_array($this->content_json)) {
                return $this->content_json;
            }

            $decoded = json_decode($this->attributes['content_json'] ?? '{}', true);

            return is_array($decoded) ? $decoded : [];
        });
    }
}
