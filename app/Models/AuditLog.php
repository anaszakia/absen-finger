<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_email', 
        'user_role',
        'action',
        'controller',
        'route',
        'method',
        'url',
        'ip',
        'user_agent',
        'status_code',
        'request_data',
        'performed_at'
    ];

    protected $casts = [
        'request_data' => 'array',
        'performed_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function getActionBadgeAttribute(): string
    {
        return match($this->action) {
            'store' => 'bg-green-100 text-green-800',
            'update' => 'bg-blue-100 text-blue-800', 
            'destroy' => 'bg-red-100 text-red-800',
            'login' => 'bg-purple-100 text-purple-800',
            'logout' => 'bg-gray-100 text-gray-800',
            default => 'bg-yellow-100 text-yellow-800'
        };
    }
    
    public function getActionTextAttribute(): string
    {
        return match($this->action) {
            'store' => 'Create',
            'update' => 'Update', 
            'destroy' => 'Delete',
            'login' => 'Login',
            'logout' => 'Logout',
            default => ucfirst($this->action)
        };
    }
}
