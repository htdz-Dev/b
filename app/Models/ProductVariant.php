<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'sku',
        'stock_quantity',
        'price_adjustment',
    ];

    protected $casts = [
        'stock_quantity' => 'integer',
        'price_adjustment' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) $this->product->price + (float) $this->price_adjustment;
    }

    public function getInStockAttribute(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function getDisplayNameAttribute(): string
    {
        $parts = [];
        if ($this->size) {
            $parts[] = "Size: {$this->size}";
        }
        if ($this->color) {
            $parts[] = "Color: {$this->color}";
        }
        return implode(', ', $parts);
    }
}
