<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('images/qr-oms-logo.png'))
            ->brandLogoHeight('8.5rem')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make('Operaciones'),
                NavigationGroup::make('Pedidos'),
                NavigationGroup::make('Menú'),
                NavigationGroup::make('Sistema')
                    ->collapsed(),
            ])
            ->navigationItems([
                NavigationItem::make('POS')
                    ->url('/pos', true)
                    ->icon('heroicon-s-currency-dollar')
                    ->group('Operaciones')
                    ->sort(1)
                    ->openUrlInNewTab(),
                NavigationItem::make('KSD')
                    ->url('/ksd', true)
                    ->icon('heroicon-s-fire')
                    ->group('Operaciones')
                    ->sort(2)
                    ->openUrlInNewTab(),
                NavigationItem::make('Gestor de Mesas')
                    ->url('/mesas', true)
                    ->icon('heroicon-s-squares-2x2')
                    ->group('Operaciones')
                    ->sort(3)
                    ->openUrlInNewTab(),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
