<?php

namespace Database\Factories;

use App\Models\Category;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'The '.fake()->firstName('female').' '.fake()->randomElement([
            'Dress', 'Blazer', 'Set', 'Trousers', 'Blouse', 'Skirt', 'Jumpsuit', 'Coat',
        ]);
        $slug = Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999);
        $price = fake()->randomElement([45000, 65000, 85000, 95000, 120000, 145000, 175000]);

        $currency = app(CurrencyService::class);

        $pool = [4625792, 13074496, 6675408, 20544951, 8180704, 9958445];

        $images = collect($pool)
            ->shuffle()
            ->take(2)
            ->map(fn (int $id) => "https://images.pexels.com/photos/{$id}/pexels-photo-{$id}.jpeg?auto=compress&cs=tinysrgb&w=900&h=1200&fit=crop")
            ->all();

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => $slug,
            'images' => $images,
            'description' => '<p>'.fake()->paragraph(3).'</p>',
            'price' => $price,
            'currency' => 'NGN',
            'price_usd' => $currency->convert($price, 'NGN', 'USD'),
            'price_gbp' => $currency->convert($price, 'NGN', 'GBP'),
            'price_eur' => $currency->convert($price, 'NGN', 'EUR'),
            'price_cad' => $currency->convert($price, 'NGN', 'CAD'),
            'price_ghs' => $currency->convert($price, 'NGN', 'GHS'),
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'in_stock' => true,
            'on_sale' => fake()->boolean(15),
        ];
    }
}
