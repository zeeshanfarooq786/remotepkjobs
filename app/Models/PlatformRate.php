<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformRate extends Model
{
    protected $fillable = [
        'platform',
        'fee_type',
        'fee_value',
        'currency',
        'effective_date',
    ];

    protected $casts = [
        'fee_value' => 'decimal:4',
        'effective_date' => 'date',
    ];

    protected $dates = [
        'effective_date',
        'created_at',
        'updated_at',
    ];
}
