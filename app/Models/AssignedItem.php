<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'personnel_id',
        'assigned_by',
        'quantity',
        'assigned_date',
        'return_date',
        'status',
        'condition_notes',
        'return_notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'assigned_date' => 'date',
            'return_date' => 'date',
        ];
    }

    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(PropertyCustodian::class, 'assigned_by');
    }
}

