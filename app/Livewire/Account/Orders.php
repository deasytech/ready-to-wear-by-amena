<?php

namespace App\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.storefront')]
#[Title('Order History')]
class Orders extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.account.orders', [
            'orders' => Auth::user()->orders()->with('items')->latest()->paginate(10),
        ]);
    }
}
