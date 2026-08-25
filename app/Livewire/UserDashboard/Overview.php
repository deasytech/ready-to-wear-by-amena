<?php

namespace App\Livewire\UserDashboard;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard')]
class Overview extends Component
{
    public function render()
    {
        $user = Auth::user();
        $orders = $user->orders()->with('items')->latest()->get();

        return view('user-dashboard.dashboard', [
            'recentOrders' => $orders->take(5),
            'activeShipments' => $orders->whereIn('status', ['processing', 'shipped']),
            'stats' => [
                'total_orders' => $orders->count(),
                'in_transit' => $orders->where('status', 'shipped')->count(),
                'delivered' => $orders->where('status', 'delivered')->count(),
                'addresses' => $user->addresses()->whereNull('order_id')->count(),
            ],
        ]);
    }
}
