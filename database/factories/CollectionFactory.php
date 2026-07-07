<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'The Noir Edit', 'Resort Season', 'Signature Tailoring', 'The Minimalist',
        ]);

        $imageId = fake()->randomElement([17945059, 20544951, 34691207, 9834550]);

        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'image' => "https://images.pexels.com/photos/{$imageId}/pexels-photo-{$imageId}.jpeg?auto=compress&cs=tinysrgb&w=1600&h=900&fit=crop",
            'description' => fake()->paragraph(2),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
