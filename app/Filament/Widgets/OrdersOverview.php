<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;

class OrdersOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
                Stat::make('Órdenes activas', Order::whereIn('status', ['pending', 'preparing', 'ready'])->count())
                    ->icon('heroicon-o-bolt')
                    ->color('primary')
                    ->description('Pendientes, en preparación o listas'),
                Stat::make('Órdenes entregadas', Order::where('status', 'delivered')->count())
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->description('Órdenes completadas'),
                Stat::make('Órdenes canceladas', Order::where('status', 'cancelled')->count())
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->description('Órdenes anuladas'),
        ];
    }
}
