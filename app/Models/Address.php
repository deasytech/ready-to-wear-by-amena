<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'label',
        'first_name',
        'last_name',
        'phone',
        'email',
        'company',
        'street_address',
        'city',
        'state',
        'zip_code',
        'country',
        'address_type',
        'is_default',
        'latitude',
        'longitude',
        'address_code',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->street_address, $this->city, $this->state, $this->zip_code, $this->country])
            ->filter()
            ->implode(', ');
    }

    public function scopeShipping($query)
    {
        return $query->where('address_type', 'shipping');
    }

    public function scopeBilling($query)
    {
        return $query->where('address_type', 'billing');
    }
}
