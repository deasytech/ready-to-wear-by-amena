<?php

namespace App\Livewire\Shop;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CurrencyService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    public Product $product;

    public ?int $selectedColorId = null;

    public ?int $selectedSizeId = null;

    public int $quantity = 1;

    public string $activeAccordion = 'details';

    public function mount(Product $product): void
    {
        $this->product = $product->load('category', 'colors', 'sizes', 'variants.color', 'variants.size');

        $this->selectedColorId = $this->product->colors->first()?->id;
        $this->selectedSizeId = $this->product->sizes->first()?->id;
    }

    #[Computed]
    public function selectedVariant(): ?ProductVariant
    {
        if ($this->product->variants->isEmpty()) {
            return null;
        }

        return $this->product->variants->first(
            fn ($variant) => $variant->color_id === $this->selectedColorId && $variant->size_id === $this->selectedSizeId
        );
    }

    #[Computed]
    public function isAvailable(): bool
    {
        if ($this->product->variants->isEmpty()) {
            return $this->product->in_stock;
        }

        return (bool) $this->selectedVariant?->inStock($this->quantity);
    }

    public function addToBag(): void
    {
        if (! $this->isAvailable) {
            $this->addError('stock', 'This selection is currently out of stock.');

            return;
        }

        app(\App\Services\CartService::class)->addItem($this->product, $this->selectedVariant, $this->quantity);

        $this->dispatch('cart-updated');
        $this->dispatch('open-cart-drawer');
    }

    public function toggleWishlist(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: false);

            return;
        }

        $wishlist = Auth::user()->wishlist()->firstOrCreate([]);
        $item = $wishlist->items()->where('product_id', $this->product->id)->first();

        if ($item) {
            $item->delete();
        } else {
            $wishlist->items()->create(['product_id' => $this->product->id]);
        }

        $this->dispatch('wishlist-updated');
    }

    public function render(CurrencyService $currencyService)
    {
        $activeCurrency = $currencyService->getCurrentCurrency();

        return view('livewire.shop.show', [
            'activeCurrency' => $activeCurrency,
            'price' => $this->selectedVariant?->price_override ?? $this->product->getPriceForCurrency($activeCurrency),
            'isAvailable' => $this->isAvailable,
            'relatedProducts' => Product::query()
                ->active()
                ->where('category_id', $this->product->category_id)
                ->where('id', '!=', $this->product->id)
                ->inRandomOrder()
                ->take(4)
                ->get(),
            'isWishlisted' => Auth::check()
                ? Auth::user()->wishlist?->items()->where('product_id', $this->product->id)->exists() ?? false
                : false,
        ])
            ->layout('components.layouts.storefront')
            ->title($this->product->name)
            ->layoutData(['description' => strip_tags($this->product->description ?? '')]);
    }
}
