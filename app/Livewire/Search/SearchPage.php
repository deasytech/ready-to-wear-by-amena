<?php

namespace App\Livewire\Search;

use App\Models\Product;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.storefront')]
#[Title('Search')]
class SearchPage extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $query = '';

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $results = collect();

        if (strlen(trim($this->query)) >= 2) {
            $results = Product::query()
                ->active()
                ->where('name', 'like', '%'.$this->query.'%')
                ->orWhereHas('category', fn ($q) => $q->where('name', 'like', '%'.$this->query.'%'))
                ->paginate(12);
        } else {
            $results = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 12);
        }

        return view('livewire.search.search-page', [
            'results' => $results,
        ]);
    }
}
