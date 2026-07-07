<?php

namespace App\Livewire\Home;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
class HomePage extends Component
{
    public function render()
    {
        $banners = Banner::active()->orderBy('id')->take(2)->get();

        return view('livewire.home.home-page', [
            'heroBanner' => $banners->first(),
            'campaignBanner' => $banners->skip(1)->first(),
            'newArrivals' => Product::query()->active()->latest()->take(4)->get(),
            'featuredCollection' => Collection::active()->orderBy('sort_order')->with(['products' => fn ($q) => $q->active()->take(4)])->first(),
            'categories' => Category::active()->whereNull('parent_id')->get(),
            'bestSellers' => Product::query()->active()->where(fn ($q) => $q->featured()->orWhere('on_sale', true))->inRandomOrder()->take(8)->get(),
        ]);
    }
}
