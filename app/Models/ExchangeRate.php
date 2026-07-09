<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'recorded_at',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'recorded_at' => 'datetime',
    ];

    protected $dates = [
        'recorded_at',
        'created_at',
        'updated_at',
    ];
}
