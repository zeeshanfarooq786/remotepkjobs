<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'company',
        'slug',
        'salary_min',
        'salary_max',
        'currency',
        'stack',
        'country',
        'remote_type',
        'source_url',
        'posted_at',
        'is_active',
    ];

    protected $casts = [
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'posted_at' => 'datetime',
        'is_active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    protected $dates = [
        'posted_at',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function salarySnapshot(): HasOne
    {
        return $this->hasOne(SalarySnapshot::class, 'stack', 'stack')
            ->whereColumn('salary_snapshots.country', 'jobs.country')
            ->ofMany('recorded_at', 'max');
    }
}
