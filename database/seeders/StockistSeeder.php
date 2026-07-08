<?php

namespace Database\Seeders;

use App\Models\Stockist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StockistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stockists = [
            [
                'name' => 'RTW Victoria Island Flagship',
                'description' => 'Our flagship boutique in the heart of Victoria Island, showcasing the full current collection alongside personal styling appointments.',
                'address' => '15A, Etim Iyang Crescent',
                'city' => 'Victoria Island',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '101241',
                'phone' => '+234 812 345 6789',
                'email' => 'vi@readytowearbyamena.com',
                'website' => 'https://readytowearbyamena.com',
                'latitude' => '6.4281',
                'longitude' => '3.4215',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'RTW Ikeja City Mall',
                'description' => 'Located inside Ikeja City Mall, offering ready-to-wear pieces and made-to-order tailoring consultations.',
                'address' => '174/194, Obafemi Awolowo Way',
                'city' => 'Ikeja',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '100271',
                'phone' => '+234 802 987 6543',
                'email' => 'ikeja@readytowearbyamena.com',
                'website' => 'https://readytowearbyamena.com',
                'latitude' => '6.6059',
                'longitude' => '3.3491',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'RTW Lekki Phase 1',
                'description' => 'A quiet, appointment-friendly showroom in Lekki Phase 1 for private fittings and seasonal previews.',
                'address' => 'Plot 12, Block 98, Omorinre Johnson Street',
                'city' => 'Lekki',
                'state' => 'Lagos',
                'country' => 'Nigeria',
                'postal_code' => '105102',
                'phone' => '+234 909 876 5432',
                'email' => 'lekki@readytowearbyamena.com',
                'website' => 'https://readytowearbyamena.com',
                'latitude' => '6.4434',
                'longitude' => '3.4727',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'RTW Abuja Wuse 2',
                'description' => 'Bringing Ready-To-Wear by Amena to the capital, with a curated edit of the current collection.',
                'address' => 'Plot 227, Adetokunbo Ademola Crescent',
                'city' => 'Abuja',
                'state' => 'FCT',
                'country' => 'Nigeria',
                'postal_code' => '900288',
                'phone' => '+234 703 456 7890',
                'email' => 'abuja@readytowearbyamena.com',
                'website' => 'https://readytowearbyamena.com',
                'latitude' => '9.0765',
                'longitude' => '7.3986',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'RTW Port Harcourt',
                'description' => 'Our Garden City showroom, offering the current collection and personal styling by appointment.',
                'address' => '45, Azikiwe Road',
                'city' => 'Port Harcourt',
                'state' => 'Rivers',
                'country' => 'Nigeria',
                'postal_code' => '500241',
                'phone' => '+234 808 234 5678',
                'email' => 'ph@readytowearbyamena.com',
                'website' => 'https://readytowearbyamena.com',
                'latitude' => '4.8242',
                'longitude' => '7.0336',
                'is_active' => true,
                'sort_order' => 5,
            ],
        ];

        foreach ($stockists as $stockist) {
            $stockist['slug'] = Str::slug($stockist['name']);
            Stockist::updateOrCreate(['slug' => $stockist['slug']], $stockist);
        }
    }
}
