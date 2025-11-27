<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category',
        'brand',
        'model',
        'serial_number',
        'unit_price',
        'quantity',
        'available_quantity',
        'unit_of_measure',
        'location',
        'status',
        'purchase_date',
        'warranty_expiry',
        'supplier',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    public function assignedItems()
    {
        return $this->hasMany(AssignedItem::class);
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}

