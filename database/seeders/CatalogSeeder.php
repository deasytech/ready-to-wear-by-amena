<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\DiscountCode;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\Size;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Real, category-relevant fashion photography (Pexels, free to use) keyed by
     * catalogue category. Products cycle through their category's pool so every
     * item gets an on-topic (if not perfectly unique) product image instead of
     * random unrelated placeholder photos.
     */
    protected const IMAGE_POOLS = [
        'Dresses' => [4625792, 13074496, 6675408, 20544951, 8180704, 9958445, 14873046, 19477367, 32335610, 27516229],
        'Two Pieces' => [12958683, 6580495, 32498728, 8484104, 8484108, 33339928],
        'Tops' => [7202800, 9834550, 7959816, 7202789, 20781335, 7203738, 20636648, 20636636, 32649060, 18504977, 31033199, 26241360],
        'Bottoms' => [7202826, 7202768, 31400265, 5253944, 8946961, 18532851, 13214674, 5478513, 19510506, 15114415, 7636101, 27786098],
        'Accessories' => [4004225, 34501351, 2986445, 36365228, 36367488, 26316185, 27174557, 31959214],
    ];

    protected static function pexelsUrl(int $id, int $width, int $height): string
    {
        return "https://images.pexels.com/photos/{$id}/pexels-photo-{$id}.jpeg?auto=compress&cs=tinysrgb&w={$width}&h={$height}&fit=crop";
    }

    public function run(): void
    {
        $this->seedBanners();
        $categories = $this->seedCategories();
        $colors = $this->seedColors();
        $sizes = $this->seedSizes();
        $shippingMethods = $this->seedShippingMethods();
        $this->seedDiscountCodes();

        $products = $this->seedProducts($categories, $colors, $sizes);
        $this->seedCollections($products);
        $this->seedSampleOrders($products, $shippingMethods);
    }

    protected function seedBanners(): void
    {
        $banners = [
            [
                'title' => 'The New Season Edit',
                'description' => 'Tailoring and fluid silhouettes, cut for a considered wardrobe.',
                'image' => static::pexelsUrl(8602141, 1920, 1080),
            ],
            [
                'title' => 'The Noir Edit',
                'description' => 'A study in restraint — black, sculpted, unmistakably RTW.',
                'image' => static::pexelsUrl(29897139, 1920, 900),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(['title' => $banner['title']], [...$banner, 'is_active' => true]);
        }
    }

    protected function seedCategories(): array
    {
        $names = ['Dresses', 'Two Pieces', 'Tops', 'Bottoms', 'Accessories'];
        $categories = [];

        foreach ($names as $name) {
            $slug = Str::slug($name);
            $categoryImage = static::pexelsUrl(static::IMAGE_POOLS[$name][0], 900, 1100);
            $categories[$name] = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'is_active' => true, 'image' => $categoryImage]
            );
        }

        return $categories;
    }

    protected function seedColors(): array
    {
        $palette = [
            'Black' => '#000000',
            'White' => '#FFFFFF',
            'Ivory' => '#F5F0E8',
            'Charcoal' => '#36454F',
            'Camel' => '#C19A6B',
        ];

        $colors = [];

        foreach ($palette as $name => $hex) {
            $colors[$name] = Color::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'hex_code' => $hex, 'is_active' => true]
            );
        }

        return $colors;
    }

    protected function seedSizes(): array
    {
        $names = ['XS', 'S', 'M', 'L', 'XL'];
        $sizes = [];

        foreach ($names as $name) {
            $sizes[$name] = Size::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'size_type' => Size::SIZES[$name] ?? $name, 'is_active' => true]
            );
        }

        return $sizes;
    }

    protected function seedShippingMethods(): array
    {
        return [
            'standard' => ShippingMethod::updateOrCreate(
                ['code' => 'standard'],
                [
                    'name' => 'Standard Shipping',
                    'description' => 'Delivered within Nigeria by road courier.',
                    'cost' => 2500,
                    'currency' => 'NGN',
                    'estimated_days_min' => 3,
                    'estimated_days_max' => 7,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            ),
            'express' => ShippingMethod::updateOrCreate(
                ['code' => 'express'],
                [
                    'name' => 'Express Shipping',
                    'description' => 'Priority courier delivery for Lagos and Abuja.',
                    'cost' => 6000,
                    'currency' => 'NGN',
                    'estimated_days_min' => 1,
                    'estimated_days_max' => 2,
                    'is_active' => true,
                    'sort_order' => 2,
                ]
            ),
        ];
    }

    protected function seedDiscountCodes(): void
    {
        DiscountCode::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'type' => 'percentage',
                'value' => 10,
                'min_order_amount' => 20000,
                'max_uses' => null,
                'is_active' => true,
            ]
        );
    }

    protected function seedProducts(array $categories, array $colors, array $sizes): array
    {
        $catalogue = [
            'Dresses' => [
                'The Amara Dress', 'The Ivory Evening Dress', 'The Bias Slip Dress',
                'The Column Maxi Dress', 'The Wrap Midi Dress', 'The Noir Cocktail Dress', 'The Aso Slip Dress',
            ],
            'Two Pieces' => [
                'Ada Silk Set', 'The Tailored Co-ord', 'The Linen Two-Piece', 'The Evening Two-Piece', 'The Wide-Leg Set',
            ],
            'Tops' => [
                'Signature Poplin Shirt', 'The Silk Camisole', 'Noir Structured Blazer',
                'The Draped Blouse', 'The Ribbed Turtleneck', 'The Oversized Shirt',
            ],
            'Bottoms' => [
                'Signature Tailored Trousers', 'The Wide-Leg Trousers', 'The Pencil Skirt',
                'The Pleated Midi Skirt', 'The Straight Leg Denim',
            ],
            'Accessories' => [
                'The Leather Belt', 'The Silk Scarf', 'The Structured Tote', 'The Statement Earrings',
            ],
        ];

        $priceTiers = [45000, 65000, 85000, 95000, 120000, 145000, 175000];
        $currency = app(CurrencyService::class);
        $products = [];
        $globalIndex = 0;

        foreach ($catalogue as $categoryName => $names) {
            $category = $categories[$categoryName];

            $pool = static::IMAGE_POOLS[$categoryName];

            foreach ($names as $index => $name) {
                $globalIndex++;
                $slug = Str::slug($name);
                $price = $priceTiers[$globalIndex % count($priceTiers)];

                $product = Product::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'images' => [
                            static::pexelsUrl($pool[$index % count($pool)], 900, 1200),
                            static::pexelsUrl($pool[($index + 1) % count($pool)], 900, 1200),
                        ],
                        'description' => $this->descriptionFor($name, $categoryName),
                        'price' => $price,
                        'currency' => 'NGN',
                        'price_usd' => $currency->convert($price, 'NGN', 'USD'),
                        'price_gbp' => $currency->convert($price, 'NGN', 'GBP'),
                        'price_eur' => $currency->convert($price, 'NGN', 'EUR'),
                        'price_cad' => $currency->convert($price, 'NGN', 'CAD'),
                        'price_ghs' => $currency->convert($price, 'NGN', 'GHS'),
                        'is_active' => true,
                        'is_featured' => $globalIndex % 5 === 0,
                        'in_stock' => true,
                        'on_sale' => $globalIndex % 7 === 0,
                    ]
                );

                // Every product offers 2-3 colours and 3-4 sizes, each combination
                // becoming a stock-tracked variant (some intentionally at 0 stock).
                $productColors = collect($colors)->shuffle()->take(rand(2, 3));
                $productSizes = collect($sizes)->slice(0, rand(3, 5));

                $product->colors()->sync($productColors->pluck('id'));
                $product->sizes()->sync($productSizes->pluck('id'));

                foreach ($productColors as $color) {
                    foreach ($productSizes as $size) {
                        ProductVariant::updateOrCreate(
                            [
                                'product_id' => $product->id,
                                'color_id' => $color->id,
                                'size_id' => $size->id,
                            ],
                            [
                                'sku' => strtoupper("RTW-{$slug}-{$color->name}-{$size->name}"),
                                'stock' => rand(0, 20),
                                'is_active' => true,
                            ]
                        );
                    }
                }

                $products[$slug] = $product;
            }
        }

        return $products;
    }

    protected function descriptionFor(string $name, string $category): string
    {
        return "<p>{$name} is cut from considered, weighty fabric and finished with clean, minimal detailing &mdash; designed to anchor a wardrobe rather than compete for attention within it.</p><p>Part of our {$category} edit, made in limited runs with close attention to fit across sizes.</p>";
    }

    protected function seedCollections(array $products): void
    {
        $collections = [
            'The Noir Edit' => [
                'slugs' => ['the-noir-cocktail-dress', 'noir-structured-blazer', 'signature-tailored-trousers', 'the-column-maxi-dress', 'the-leather-belt'],
                'image' => 17945059,
            ],
            'Resort Season' => [
                'slugs' => ['the-bias-slip-dress', 'the-linen-two-piece', 'the-wrap-midi-dress', 'the-silk-scarf', 'the-silk-camisole'],
                'image' => 20544951,
            ],
            'Signature Tailoring' => [
                'slugs' => ['ada-silk-set', 'the-tailored-co-ord', 'signature-poplin-shirt', 'the-wide-leg-trousers', 'the-pencil-skirt'],
                'image' => 34691207,
            ],
        ];

        foreach ($collections as $name => $data) {
            $slugs = $data['slugs'];

            $collection = Collection::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'image' => static::pexelsUrl($data['image'], 1600, 900),
                    'description' => "A curated edit built around {$name}.",
                    'is_active' => true,
                ]
            );

            $sync = [];
            foreach ($slugs as $sortOrder => $slug) {
                if (isset($products[$slug])) {
                    $sync[$products[$slug]->id] = ['sort_order' => $sortOrder];
                }
            }

            $collection->products()->sync($sync);
        }
    }

    protected function seedSampleOrders(array $products, array $shippingMethods): void
    {
        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            ['name' => 'Amaka Test Customer', 'email_verified_at' => now(), 'password' => bcrypt('password')]
        );

        $sampleSlugs = array_slice(array_keys($products), 0, 6);
        $statuses = ['delivered', 'processing', 'pending'];

        foreach ($statuses as $index => $status) {
            $product = $products[$sampleSlugs[$index]];
            $quantity = rand(1, 2);
            $unitAmount = (float) $product->price;
            $shipping = $shippingMethods['standard'];
            $subtotal = $unitAmount * $quantity;
            $grandTotal = $subtotal + $shipping->cost;

            $order = Order::create([
                'user_id' => $customer->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'grand_total' => $grandTotal,
                'currency' => 'NGN',
                'payment_method' => 'paystack',
                'payment_status' => $status === 'pending' ? 'pending' : 'paid',
                'status' => $status,
                'shipping_amount' => $shipping->cost,
                'shipping_method' => $shipping->code,
                'shipping_method_id' => $shipping->id,
                'created_at' => now()->subDays(10 - $index * 3),
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => $quantity,
                'unit_amount' => $unitAmount,
                'total_amount' => $unitAmount * $quantity,
            ]);

            $order->addresses()->create([
                'user_id' => $customer->id,
                'first_name' => 'Amaka',
                'last_name' => 'Okafor',
                'phone' => '+2348012345678',
                'email' => $customer->email,
                'street_address' => '10 Admiralty Way',
                'city' => 'Lekki',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'address_type' => 'shipping',
                'is_default' => true,
            ]);
        }
    }
}
