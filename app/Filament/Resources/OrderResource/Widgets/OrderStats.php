<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenue = Order::query()->where('payment_status', 'paid')->sum('grand_total');

        return [
            Stat::make('Total Revenue', '₦'.number_format($totalRevenue, 2))
                ->color('success')
                ->icon('heroicon-o-banknotes'),
            Stat::make('Total Orders', Order::query()->count())
                ->color('primary')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Pending Orders', Order::query()->where('status', 'pending')->count())
                ->color('warning')
                ->icon('heroicon-o-clock'),
            Stat::make('Completed Orders', Order::query()->where('status', 'delivered')->count())
                ->color('info')
                ->icon('heroicon-o-check-badge'),
        ];
    }
}
