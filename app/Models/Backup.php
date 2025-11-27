<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'filename',
        'file_path',
        'file_size',
        'type',
        'status',
        'notes',
        'restored_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'restored_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(Ict::class, 'created_by');
    }
}

