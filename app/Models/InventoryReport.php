<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'generated_by',
        'report_type',
        'title',
        'description',
        'filters',
        'data',
        'file_path',
        'format',
        'date_from',
        'date_to',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'data' => 'array',
            'date_from' => 'date',
            'date_to' => 'date',
        ];
    }

    public function generator()
    {
        return $this->belongsTo(PropertyCustodian::class, 'generated_by');
    }
}

