<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AttendanceMachine extends Model
{
    protected $fillable = [
        'name',
        'ip_address',
        'port',
        'serial_number',
        'location',
        'is_active',
        'description',
    ];

    protected $casts = [
        'port' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all attendances from this machine
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get active machines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Test connection to the machine
     */
    public function testConnection()
    {
        try {
            $zk = new \Rats\Zkteco\Lib\ZKTeco($this->ip_address, $this->port);
            $connected = $zk->connect();
            $zk->disconnect();
            return $connected;
        } catch (\Exception $e) {
            return false;
        }
    }
}
