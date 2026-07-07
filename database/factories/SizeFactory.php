<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Size>
 */
class SizeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(\App\Models\Size::SIZES));

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'size_type' => \App\Models\Size::SIZES[$name],
            'is_active' => true,
        ];
    }
}
