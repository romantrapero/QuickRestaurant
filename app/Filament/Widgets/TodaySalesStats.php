<?php

namespace App\Filament\Widgets;

use App\Models\CashShift;
use App\Models\Order;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodaySalesStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getCards(): array
    {
        $today = now()->startOfDay();

        // Ventas de hoy (pagos realizados)
        $todaySales = Payment::where('created_at', '>=', $today)->sum('amount');

        // Órdenes de hoy
        $todayOrders = Order::where('created_at', '>=', $today)->count();

        // En caja (turno actual)
        $currentShift = CashShift::current();
        $enCaja = $currentShift ? $currentShift->expected_cash : 0;

        return [
            Stat::make('Ventas Hoy', '$'.number_format($todaySales, 2))
                ->icon('heroicon-o-currency-dollar')
                ->color('success')
                ->description('Total cobrado hoy'),

            Stat::make('Órdenes Hoy', $todayOrders)
                ->icon('heroicon-o-shopping-bag')
                ->color('primary')
                ->description('Órdenes creadas'),

            Stat::make('En Caja', '$'.number_format($enCaja, 2))
                ->icon('heroicon-o-banknotes')
                ->color($currentShift ? 'success' : 'gray')
                ->description($currentShift ? 'Turno abierto' : 'Sin turno abierto'),
        ];
    }
}
