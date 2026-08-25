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
        'color_id',
        'size_id',
        'sku',
        'stock',
        'price_override',
        'price_override_usd',
        'price_override_gbp',
        'price_override_eur',
        'price_override_cad',
        'price_override_ghs',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'price_override' => 'decimal:2',
            'price_override_usd' => 'decimal:2',
            'price_override_gbp' => 'decimal:2',
            'price_override_eur' => 'decimal:2',
            'price_override_cad' => 'decimal:2',
            'price_override_ghs' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    /**
     * The size-specific price override for a currency, or null if this variant
     * doesn't override the product's price in that currency.
     */
    public function getPriceOverrideForCurrency(string $currency): ?float
    {
        $currency = strtolower($currency);
        $column = $currency === 'ngn' ? 'price_override' : "price_override_{$currency}";

        $value = $this->{$column} ?? null;

        return $value !== null ? (float) $value : null;
    }

    public function inStock(int $quantity = 1): bool
    {
        return $this->is_active && $this->stock >= $quantity;
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('stock', '>', 0);
    }
}
