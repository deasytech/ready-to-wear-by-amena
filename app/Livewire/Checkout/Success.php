<?php

namespace App\Livewire\Checkout;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.storefront')]
#[Title('Order Confirmed')]
class Success extends Component
{
    public ?Order $order = null;

    public function mount(): void
    {
        $orderId = session('success_order_id');
        $this->order = $orderId ? Order::with('items')->find($orderId) : null;
    }

    public function render()
    {
        return view('livewire.checkout.success');
    }
}
