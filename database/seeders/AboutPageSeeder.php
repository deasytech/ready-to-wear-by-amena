<?php

namespace Database\Seeders;

use App\Models\About;
use Illuminate\Database\Seeder;

class AboutPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        About::updateOrCreate(['section_name' => 'main_about'], [
            'title' => 'About Us',
            'content' => '<p class="short-desc mr-2">Ready To Wear by Amena is a womenswear label built on precise tailoring, considered fabrics, and a refusal to chase trends. Every piece is designed to be worn, re-worn, and lived in &mdash; not saved for one occasion and forgotten.</p>

<p class="short-desc">We work in a restrained palette of black, white and neutral tones, letting cut and construction carry each collection rather than colour or print. It is a quieter kind of luxury, built for women who dress for themselves first.</p>

<p class="short-desc mb-0">From tailored blazers to fluid evening silhouettes, every garment is developed in small runs with close attention to fit across sizes, so the same rigour that goes into a made-to-measure piece shows up in ready-to-wear.</p>',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        About::updateOrCreate(['section_name' => 'our_story'], [
            'title' => 'Our Story',
            'content' => '<p>Ready To Wear by Amena began with a simple frustration: too much of what filled our own closets felt disposable &mdash; fast, trend-driven, forgotten within a season. Amena set out to design the opposite. Pieces cut with intention, finished properly, and built to anchor a wardrobe rather than clutter it.</p>

<p>What started as a small made-to-order tailoring practice has grown into a full ready-to-wear label, but the founding principle has not changed: fewer, better pieces, made well enough to outlast the season they were designed for.</p>

<p>Every collection is still designed the same way it was from day one &mdash; sketched, fitted, and refined on real women, in real sizes, before a single unit goes into production.</p>',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        About::updateOrCreate(['section_name' => 'our_culture'], [
            'title' => 'Our Culture',
            'content' => '<p>Ready To Wear by Amena is a small, deliberately-run studio. We produce in limited runs rather than mass quantities, which means more control over fit, finishing, and the working conditions behind every garment.</p>

<p>We favour natural and responsibly-sourced fabrics &mdash; silk, wool, cotton poplin &mdash; and work with a small network of pattern makers and tailors we know by name.</p>

<p>Our belief is simple: a considered wardrobe is a form of self-respect. We would rather make one dress you wear for five years than five dresses you wear once.</p>',
            'sort_order' => 3,
            'is_active' => true,
        ]);
    }
}
