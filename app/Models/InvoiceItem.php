<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $item): void {
            $quantity = (float) ($item->quantity ?? 0);
            $unitPrice = (float) ($item->unit_price ?? 0);

            $item->total_price = round($quantity * $unitPrice, 2);
        });
    }

    protected $fillable = [
        'invoice_id', 'inventory_id', 'description',
        'quantity', 'unit_price', 'total_price',
    ];

    protected $casts = [
        'unit_price'  => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): ?string => $this->sanitizeUtf8($value),
            set: fn (?string $value): ?string => $this->sanitizeUtf8($value),
        );
    }

    protected function sanitizeUtf8(?string $value): ?string
    {
        if ($value === null || mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
