<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DcpPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'batch_name',
        'quantity',
        'package_count',
        'delivery_date',
        'delivery_status',
        'installation_status',
        'details',
        'remarks',
        'dr_number',
        'dr_filename',
        'ptr_number',
        'ptr_filename',
        'iar_number',
        'iar_filename',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'quantity' => 'integer',
            'package_count' => 'integer',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function creator()
    {
        return $this->belongsTo(Ict::class, 'created_by');
    }
}

