<?php

namespace App\Livewire\UserDashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('My Orders')]
class Orders extends Component
{
    use WithPagination;

    public function render()
    {
        return view('user-dashboard.orders', [
            'orders' => Auth::user()->orders()->with('items')->latest()->paginate(10),
        ]);
    }
}
