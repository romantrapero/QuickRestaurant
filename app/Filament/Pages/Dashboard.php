<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use App\Models\Order;
use App\Models\Dish;
use App\Models\Category;
use App\Models\User;

class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        // Agrega los widgets que deseas mostrar en el panel de control
    }
}
