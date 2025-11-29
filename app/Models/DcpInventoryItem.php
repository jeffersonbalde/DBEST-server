<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DcpInventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'dcp_package_id',
        'batch_name',
        'category',
        'description',
        'manufacturer',
        'model',
        'serial_number',
        'unit_of_measure',
        'unit_value',
        'quantity',
        'property_no',
        'personnel_id',
        'condition_status',
        'last_checked_at',
        'validation_status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'unit_value' => 'decimal:2',
            'quantity' => 'integer',
            'last_checked_at' => 'date',
        ];
    }

    public function package()
    {
        return $this->belongsTo(DcpPackage::class, 'dcp_package_id');
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}


