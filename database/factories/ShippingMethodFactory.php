<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Standard Shipping',
            'code' => 'standard-'.fake()->unique()->numberBetween(1, 99999),
            'cost' => 2500,
            'estimated_days_min' => 3,
            'estimated_days_max' => 7,
            'is_active' => true,
        ];
    }
}
