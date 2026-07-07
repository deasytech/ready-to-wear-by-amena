<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'images',
        'video',
        'description',
        'price',
        'currency',
        'price_usd',
        'price_gbp',
        'price_eur',
        'price_cad',
        'price_ghs',
        'package_category_id',
        'package_dimension',
        'is_active',
        'is_featured',
        'in_stock',
        'on_sale',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'package_dimension' => 'array',
            'price' => 'decimal:2',
            'price_usd' => 'decimal:2',
            'price_gbp' => 'decimal:2',
            'price_eur' => 'decimal:2',
            'price_cad' => 'decimal:2',
            'price_ghs' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'in_stock' => 'boolean',
            'on_sale' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function colors(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }

    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(Size::class, 'product_size');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_product')->withPivot('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function getFirstImageAttribute(): ?string
    {
        return Media::url($this->images[0] ?? null);
    }

    public function getSecondImageAttribute(): ?string
    {
        return Media::url($this->images[1] ?? null);
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => Media::url($path))
            ->all();
    }

    public function getPriceForCurrency(string $currency): float
    {
        $currency = strtolower($currency);
        $column = $currency === 'ngn' ? 'price' : "price_{$currency}";

        return (float) ($this->{$column} ?? $this->price);
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->variants->isNotEmpty()) {
            return (int) $this->variants->sum('stock');
        }

        return $this->in_stock ? 1 : 0;
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ($this->variants->isNotEmpty() ? $this->total_stock > 0 : $this->in_stock);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOnSale($query)
    {
        return $query->where('on_sale', true);
    }

    public function scopeNewArrivals($query)
    {
        return $query->orderByDesc('created_at');
    }
}
