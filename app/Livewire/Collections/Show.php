<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use App\Models\Size;
use App\Services\CurrencyService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Show extends Component
{
    use WithPagination;

    public Collection $collection;

    #[Url]
    public array $sizes = [];

    #[Url]
    public string $availability = 'all';

    #[Url]
    public string $sort = 'newest';

    public function mount(Collection $collection): void
    {
        $this->collection = $collection;
    }

    public function toggleSize(string $slug): void
    {
        if (in_array($slug, $this->sizes)) {
            $this->sizes = array_values(array_diff($this->sizes, [$slug]));
        } else {
            $this->sizes[] = $slug;
        }

        $this->resetPage();
    }

    public function render(CurrencyService $currencyService)
    {
        $query = $this->collection->products()->active();

        if (! empty($this->sizes)) {
            $query->whereHas('sizes', fn ($q) => $q->whereIn('slug', $this->sizes));
        }

        match ($this->availability) {
            'in_stock' => $query->where('in_stock', true),
            'sale' => $query->where('on_sale', true),
            default => null,
        };

        match ($this->sort) {
            'price_low' => $query->reorder('price', 'asc'),
            'price_high' => $query->reorder('price', 'desc'),
            'popularity' => $query->withCount('orderItems')->reorder('order_items_count', 'desc'),
            default => null,
        };

        return view('livewire.collections.show', [
            'products' => $query->paginate(12),
            'availableSizes' => Size::active()->orderBy('id')->get(),
            'activeCurrency' => $currencyService->getCurrentCurrency(),
        ])
            ->layout('components.layouts.storefront')
            ->title($this->collection->name)
            ->layoutData(['description' => $this->collection->description]);
    }
}
