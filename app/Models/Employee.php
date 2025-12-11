<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'email',
        'phone',
        'address',
        'position',
        'department',
        'join_date',
        'basic_salary',
        'is_active',
        'photo',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get all attendances for this employee
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get active employees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
