<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'name',
        'description',
        'category',
        'category_id',
        'brand',
        'model',
        'serial_number',
        'unit_price',
        'quantity',
        'available_quantity',
        'unit_of_measure',
        'location',
        'status',
        'tracking_mode',
        'image_path',
        'purchase_date',
        'warranty_expiry',
        'supplier',
        'notes',
        'personnel_id',
    ];

    protected $appends = [
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'available_quantity' => 'integer',
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
        ];
    }

    /**
     * @return BelongsTo<InventoryCategory, InventoryItem>
     */
    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }

    public function assignedItems()
    {
        return $this->hasMany(AssignedItem::class);
    }

    public function personnel()
    {
        return $this->belongsTo(Personnel::class, 'personnel_id');
    }

    public function getTotalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }

        return url(Storage::url($this->image_path));
    }

    public static function generateItemCode(string $trackingMode = 'detailed'): string
    {
        $prefix = $trackingMode === 'basic' ? 'BASIC' : 'INV';

        return sprintf(
            '%s-%s-%s',
            strtoupper($prefix),
            now()->format('YmdHis'),
            Str::upper(Str::random(4))
        );
    }
}

