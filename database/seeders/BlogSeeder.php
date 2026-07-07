<?php

namespace Database\Seeders;

use App\Models\Blog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $blogPosts = [
            [
                'title' => 'Building a Capsule Wardrobe with Ready To Wear by Amena',
                'excerpt' => 'A considered edit of versatile, well-made pieces will always outlast a closet full of trends. Here is how we think about building one.',
                'content' => '<h2>Fewer, Better Pieces</h2>
<p>A capsule wardrobe is not about owning less for its own sake &mdash; it is about owning pieces that work harder. Every silhouette in our collections is designed to move between occasions: a tailored trouser that takes you from a morning meeting to a dinner reservation, a slip dress that layers under a blazer or stands alone.</p>

<h2>Start With Neutrals</h2>
<p>Black, ivory, camel and charcoal form the backbone of every considered wardrobe. Building from a neutral palette means every new piece you add earns its place, rather than competing for attention.</p>

<h2>Invest in Construction</h2>
<p>Look closely at how a garment is finished &mdash; the drape of a fabric, the precision of a seam, the weight of a button. These details are what separate a piece you wear for one season from one you return to for years.</p>',
                'featured_image' => null,
                'author' => 'RTW Editorial',
                'category' => 'Style Guide',
                'tags' => ['capsule wardrobe', 'style', 'minimalism'],
                'status' => 'published',
                'published_at' => now()->subDays(6),
                'views' => 412,
            ],
            [
                'title' => 'Caring for Silk, Wool and Tailored Fabrics',
                'excerpt' => 'Our pieces are made to last well beyond a single season. A short guide to keeping them that way.',
                'content' => '<h2>Read the Label First</h2>
<p>Every Ready To Wear by Amena piece is finished with fabric-specific care instructions. Following them is the single most effective way to protect your investment.</p>

<h2>Silk</h2>
<p>Hand wash in cold water with a gentle detergent, or dry clean for structured silk pieces. Always air dry away from direct sunlight to preserve colour.</p>

<h2>Tailoring</h2>
<p>Structured blazers and trousers hold their shape best when hung on a proper shoulder-form hanger and steamed rather than ironed directly.</p>

<h2>Knitwear</h2>
<p>Fold, never hang, to avoid stretching at the shoulders. Store with cedar blocks to keep moths away between seasons.</p>',
                'featured_image' => null,
                'author' => 'RTW Editorial',
                'category' => 'Fabric Care',
                'tags' => ['fabric care', 'silk', 'tailoring'],
                'status' => 'published',
                'published_at' => now()->subDays(3),
                'views' => 298,
            ],
            [
                'title' => 'This Season\'s Edit: Tailoring Meets Ease',
                'excerpt' => 'A look inside the thinking behind our latest collection, where sharp tailoring meets relaxed, wearable silhouettes.',
                'content' => '<h2>A Considered Contrast</h2>
<p>This season we set out to pair the precision of tailoring with the ease of softer, fluid fabrics. The result is a collection built for a woman who moves between the boardroom and the evening without changing her wardrobe philosophy.</p>

<h2>Key Pieces</h2>
<p>Structured blazers are cut with a longer, leaner line. Trousers sit higher at the waist with a relaxed leg. Dresses lean into bias-cut silhouettes that skim rather than cling.</p>

<h2>How to Style It</h2>
<p>Anchor any look with a single statement piece and let the rest of the outfit stay quiet. Confidence, not clutter, is the point.</p>',
                'featured_image' => null,
                'author' => 'Amena',
                'category' => 'Collections',
                'tags' => ['new collection', 'tailoring', 'editorial'],
                'status' => 'published',
                'published_at' => now()->subDay(),
                'views' => 156,
            ],
            [
                'title' => 'Notes on Sizing and Fit',
                'excerpt' => 'A practical guide to reading our size chart and choosing the right fit across dresses, separates and tailoring.',
                'content' => '<h2>Start With Your Measurements</h2>
<p>Every product page includes a size guide with bust, waist and hip measurements for XS through XL. We recommend measuring yourself rather than sizing to a label from another brand.</p>

<h2>Between Sizes?</h2>
<p>For structured tailoring, we recommend sizing up for comfort through the shoulder and waist. For fluid, bias-cut pieces, sizing down slightly gives a more considered drape.</p>

<h2>Still Unsure?</h2>
<p>Our customer care team is always available to help you find the right fit before you order.</p>',
                'featured_image' => null,
                'author' => 'RTW Editorial',
                'category' => 'Customer Care',
                'tags' => ['sizing', 'fit guide'],
                'status' => 'draft',
                'published_at' => null,
                'views' => 0,
            ],
        ];

        foreach ($blogPosts as $post) {
            $post['slug'] = Str::slug($post['title']);
            Blog::updateOrCreate(['slug' => $post['slug']], $post);
        }
    }
}
