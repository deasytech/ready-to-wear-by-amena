<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@readytowearbyamena.com'],
            ['name' => 'Amena', 'email_verified_at' => now(), 'password' => bcrypt('1!rtwRTW10000')]
        );

        $this->call(CatalogSeeder::class);
        $this->call(BlogSeeder::class);
        $this->call(StockistSeeder::class);
        $this->call(AboutPageSeeder::class);
    }
}
