<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use App\Models\Size;
use App\Services\CurrencyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.storefront')]
#[Title('Shop')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public ?string $category = null;

    #[Url]
    public array $sizes = [];

    #[Url]
    public ?float $minPrice = null;

    #[Url]
    public ?float $maxPrice = null;

    #[Url]
    public string $availability = 'all';

    #[Url]
    public string $sort = 'newest';

    public bool $showFilters = false;

    public bool $showSort = false;

    public function updating($property): void
    {
        if (in_array($property, ['category', 'sizes', 'minPrice', 'maxPrice', 'availability', 'sort'])) {
            $this->resetPage();
        }
    }

    public function toggleSize(string $slug): void
    {
        if (in_array($slug, $this->sizes)) {
            $this->sizes = array_values(array_diff($this->sizes, [$slug]));
        } else {
            $this->sizes[] = $slug;
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['category', 'sizes', 'minPrice', 'maxPrice', 'availability', 'sort']);
    }

    public function render(CurrencyService $currencyService)
    {
        $query = Product::query()->active()->with('category');

        if ($this->category) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $this->category));
        }

        if (! empty($this->sizes)) {
            $query->whereHas('sizes', fn ($q) => $q->whereIn('slug', $this->sizes));
        }

        if ($this->minPrice !== null) {
            $query->where('price', '>=', $this->minPrice);
        }

        if ($this->maxPrice !== null) {
            $query->where('price', '<=', $this->maxPrice);
        }

        match ($this->availability) {
            'in_stock' => $query->where('in_stock', true),
            'sale' => $query->where('on_sale', true),
            default => null,
        };

        match ($this->sort) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'popularity' => $query->withCount('orderItems')->orderByDesc('order_items_count'),
            default => $query->latest(),
        };

        return view('livewire.shop.index', [
            'products' => $query->paginate(12),
            'categories' => Category::active()->whereNull('parent_id')->get(),
            'availableSizes' => Size::active()->orderBy('id')->get(),
            'activeCurrency' => $currencyService->getCurrentCurrency(),
        ]);
    }
}
