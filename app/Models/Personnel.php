<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Personnel extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'personnel';

    protected $fillable = [
        'employee_id',
        'username',
        'id_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'avatar_path',
        'department',
        'position',
        'subject_area',
        'employment_status',
        'employment_level',
        'rating',
        'type',
        'notes',
        'is_active',
        'password',
        'deactivation_reason',
        'deactivated_by',
        'deactivated_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'deactivated_at' => 'datetime',
        ];
    }

    protected $appends = [
        'avatar_url',
    ];

    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar_path) {
            return null;
        }

        return asset('storage/' . $this->avatar_path);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function assignedItems()
    {
        return $this->hasMany(AssignedItem::class);
    }
}

