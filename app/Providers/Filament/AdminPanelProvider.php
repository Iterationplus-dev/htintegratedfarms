<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\EnquiryStats;
use App\Filament\Widgets\LatestEnquiries;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
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

/** Staff admin panel at /admin, themed with the design tokens (green-700 primary, navy info, DM Sans). */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->passwordReset()
            ->brandName('H & T Integrated Farms')
            ->brandLogo(fn () => asset('apple-touch-icon.png'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('favicon-32.png'))
            ->font('DM Sans')
            ->colors([
                'primary' => Color::hex('#1B6236'),
                'info' => Color::hex('#1B5E86'),
                'gray' => Color::Stone,
            ])
            ->navigationGroups([
                NavigationGroup::make('Sales'),
                NavigationGroup::make('Catalogue'),
                NavigationGroup::make('Content'),
                NavigationGroup::make('Settings'),
            ])
            ->databaseNotifications(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->widgets([
                AccountWidget::class,
                EnquiryStats::class,
                LatestEnquiries::class,
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
            ]);
    }
}
