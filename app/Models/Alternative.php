<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alternative extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'paid_tool',
        'slug',
        'open_tool',
        'category',
        'github_stars',
        'github_forks',
        'open_issues',
        'language',
        'last_commit',
        'monthly_cost_paid',
        'docker_support',
        'php_version_req',
        'laravel_compatible',
        'description',
        'comparison_json',
    ];

    protected $casts = [
        'github_stars' => 'integer',
        'github_forks' => 'integer',
        'open_issues' => 'integer',
        'last_commit' => 'datetime',
        'monthly_cost_paid' => 'decimal:2',
        'docker_support' => 'boolean',
        'laravel_compatible' => 'boolean',
        'comparison_json' => 'array',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'last_commit',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    protected function comparison(): Attribute
    {
        return Attribute::get(function () {
            if (is_array($this->comparison_json)) {
                return $this->comparison_json;
            }

            $decoded = json_decode($this->attributes['comparison_json'] ?? '{}', true);

            return is_array($decoded) ? $decoded : [];
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
