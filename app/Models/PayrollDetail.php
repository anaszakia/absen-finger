<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    protected $fillable = [
        'payroll_id',
        'type',
        'name',
        'calculation_type',
        'amount',
        'quantity',
        'total',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
