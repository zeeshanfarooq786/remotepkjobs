<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalarySnapshot extends Model
{
    protected $fillable = [
        'stack',
        'country',
        'avg_salary',
        'sample_size',
        'recorded_at',
    ];

    protected $casts = [
        'avg_salary' => 'integer',
        'sample_size' => 'integer',
        'recorded_at' => 'datetime',
    ];

    protected $dates = [
        'recorded_at',
        'created_at',
        'updated_at',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'stack', 'stack')
            ->whereColumn('jobs.country', 'salary_snapshots.country');
    }
}
