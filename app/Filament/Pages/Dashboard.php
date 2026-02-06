<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PaymentMethodsChart;
use App\Filament\Widgets\RecentPayments;
use App\Filament\Widgets\TodaySalesStats;
use App\Filament\Widgets\WeeklySalesChart;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            TodaySalesStats::class,
            WeeklySalesChart::class,
            PaymentMethodsChart::class,
            RecentPayments::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
