<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deduction extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
        'percentage',
        'auto_calculate',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'auto_calculate' => 'boolean',
        'is_active' => 'boolean',
    ];
}
