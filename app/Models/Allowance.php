<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Allowance extends Model
{
    protected $fillable = [
        'name',
        'type',
        'amount',
        'percentage',
        'requires_approval',
        'description',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];
}
