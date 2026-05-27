<?php

namespace App\Providers\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Caresome\FilamentAuthDesigner\AuthDesignerPlugin;
use Caresome\FilamentAuthDesigner\Data\AuthPageConfig;
use Caresome\FilamentAuthDesigner\Enums\MediaPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Qalainau\UniverSheet\UniverSheetPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->colors([
                'primary' => Color::hex('#8B1A4B'),
                'danger' => Color::hex('#DC2626'),
                'info' => Color::hex('#0EA5E9'),
                'success' => Color::hex('#16A34A'),
                'warning' => Color::hex('#F59E0B'),
            ])
            ->brandName('Hotel Bugambilias')
            ->brandLogo(asset('img/logo-horizontal.png'))
            ->brandLogoHeight('2.5rem')
            ->favicon(asset('img/hotel-icon.png'))
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(true)
            ->maxContentWidth('full')
            ->collapsibleNavigationGroups(true)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('15s')
            ->profile()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('9rem')
            ->plugins(array_filter([
                AuthDesignerPlugin::make()
                    ->login(
                        fn (AuthPageConfig $config) => $config
                            ->media(asset('img/pool-front-view.jpg'))
                            ->mediaPosition(MediaPosition::Right),
                    ),
                FilamentShieldPlugin::make(),
                class_exists(UniverSheetPlugin::class) ? UniverSheetPlugin::make() : null,
            ]))
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
            ]);
    }
}
