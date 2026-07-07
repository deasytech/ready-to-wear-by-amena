<?php

namespace App\Livewire\Account;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('My Account')]
class Overview extends Component
{
    public function render()
    {
        return view('livewire.account.overview', [
            'recentOrders' => Auth::user()->orders()->latest()->limit(3)->get(),
        ]);
    }
}
