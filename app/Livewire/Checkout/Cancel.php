<?php

namespace App\Livewire\Checkout;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Payment Cancelled')]
class Cancel extends Component
{
    public function render()
    {
        return view('livewire.checkout.cancel');
    }
}
