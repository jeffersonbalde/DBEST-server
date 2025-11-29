<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PropertyCustodian extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'property_custodian';

    protected $fillable = [
        'employee_id',
        'username',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'position',
        'avatar_path',
        'school_id',
        'is_active',
        'deactivation_reason',
        'deactivated_by',
        'deactivated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function assignedItems()
    {
        return $this->hasMany(AssignedItem::class, 'assigned_by');
    }

    public function generatedReports()
    {
        return $this->hasMany(InventoryReport::class, 'generated_by');
    }
}

