<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'cost',
        'currency',
        'estimated_days_min',
        'estimated_days_max',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getEstimatedDeliveryAttribute(): ?string
    {
        if (! $this->estimated_days_min) {
            return null;
        }

        return $this->estimated_days_max && $this->estimated_days_max !== $this->estimated_days_min
            ? "{$this->estimated_days_min}-{$this->estimated_days_max} business days"
            : "{$this->estimated_days_min} business days";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
