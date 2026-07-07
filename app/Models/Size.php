<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Size extends Model
{
    use HasFactory;

    public const SIZES = [
        'XS' => 'Extra Small',
        'S' => 'Small',
        'M' => 'Medium',
        'L' => 'Large',
        'XL' => 'Extra Large',
        'XXL' => 'Double Extra Large',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'size_type',
        'size_chart',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_size');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
