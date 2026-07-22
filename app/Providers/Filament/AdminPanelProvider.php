<?php

namespace App\Providers\Filament;

use App\Http\Middleware\RequerirAdmin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Enums\ThemeMode;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        /** @var string $hotelName */
        $hotelName = config('hotel.name', '');
        /** @var string $hotelLogo */
        $hotelLogo = config('hotel.logo', '');
        /** @var string $hotelIcon */
        $hotelIcon = config('hotel.icon', '');

        return $panel
            ->default()
            ->id('admin')
            ->plugin(FilamentShieldPlugin::make()->navigationGroup('Seguridad'))
            ->path('admin')
            ->login()
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors([
                'primary' => Color::hex('#6b003e'),
                'danger' => Color::hex('#DC2626'),
                'info' => Color::hex('#0EA5E9'),
                'success' => Color::hex('#16A34A'),
                'warning' => Color::hex('#F59E0B'),
            ])
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->brandName(strval($hotelName))
            ->brandLogo(asset('images/logo-dark.png'))
            ->darkModeBrandLogo(asset('images/logo-claro.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset(strval($hotelIcon)))
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs()
            ->maxContentWidth('full')
            ->collapsibleNavigationGroups()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RequerirAdmin::class,
            ]);
    }
}
