<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'inventory';

    protected $fillable = [
        'business_id', 'inventory_category_id', 'name', 'sku', 'barcode',
        'cost_price', 'selling_price', 'quantity_on_hand', 'reorder_level',
        'unit', 'is_active', 'description',
    ];

    protected $casts = [
        'cost_price'     => 'decimal:2',
        'selling_price'  => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InventoryCategory::class, 'inventory_category_id');
    }

    public function isLowStock(): bool
    {
        return $this->quantity_on_hand <= $this->reorder_level;
    }
}
