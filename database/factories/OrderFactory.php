<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'subtotal' => 30000,
            'discount_amount' => 0,
            'grand_total' => 32500,
            'currency' => 'NGN',
            'payment_method' => 'paystack',
            'payment_status' => 'pending',
            'status' => 'pending',
            'shipping_amount' => 2500,
        ];
    }
}
