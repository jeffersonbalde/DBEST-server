<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'deped_code',
        'region',
        'division',
        'district',
        'address',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'avatar_url',
        'avatar_path',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getAvatarUrlAttribute($value)
    {
        if ($this->avatar_path) {
            $cleanPath = ltrim(str_replace('\\', '/', $this->avatar_path), '/');

            if (str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = substr($cleanPath, strlen('storage/'));
            }

            return asset('storage/' . $cleanPath);
        }

        return $value;
    }

    public function propertyCustodians()
    {
        return $this->hasMany(PropertyCustodian::class);
    }

    public function dcpPackages()
    {
        return $this->hasMany(DcpPackage::class);
    }
}

