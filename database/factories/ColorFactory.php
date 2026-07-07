<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Color>
 */
class ColorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$name, $hex] = fake()->unique()->randomElement([
            ['Black', '#000000'],
            ['White', '#FFFFFF'],
            ['Ivory', '#F5F0E8'],
            ['Beige', '#E8DFD3'],
            ['Charcoal', '#36454F'],
            ['Camel', '#C19A6B'],
            ['Nude', '#E3BC9A'],
            ['Stone Grey', '#9C9C9C'],
        ]);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'hex_code' => $hex,
            'is_active' => true,
        ];
    }
}
