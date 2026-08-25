<?php

namespace App\Livewire\UserDashboard;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Order Details')]
class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $this->order = $order->load('items', 'address');
    }

    public function render()
    {
        $this->order->refresh();

        return view('user-dashboard.order-show');
    }
}
