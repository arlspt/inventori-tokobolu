<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use App\Filament\Widgets\DashboardFilter;
use App\Filament\Widgets\DashboardStats;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber, // Ubah warna utama di sini
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                // Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                DashboardFilter::class,
                DashboardStats::class,
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
            ->renderHook(
                'panels::head.end',
                fn() => '<style>

/* ===== SIDEBAR BASE ===== */
.fi-sidebar {
    background: #ffffff !important;
box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
}

/* ===== MENU ITEM ===== */
.fi-sidebar nav a {
    border-radius: 10px;
    margin: 4px 8px;
    padding: 6px 12px;
    font-weight: 500;
    color: #374151 !important;
    transition: all 0.2s ease;
}

/* ===== HOVER EFFECT ===== */
.fi-sidebar nav a:hover {
    background: #f1f5f9 !important;
    transform: translateX(3px);
}

/* ===== ACTIVE MENU ===== */
.fi-sidebar nav a[aria-current="page"] {
    background: linear-gradient(90deg, #e0f2fe, #f0f9ff) !important;
    color: #0284c7 !important;
    font-weight: 600;
    position: relative;
}

/* ===== ACTIVE INDICATOR (BAR KIRI) ===== */
.fi-sidebar nav a[aria-current="page"]::before {
    content: "";
    position: absolute;
    left: -8px;
    top: 8px;
    bottom: 8px;
    width: 4px;
    border-radius: 4px;
    background: #0284c7;
}

/* ===== ICON STYLE ===== */
.fi-sidebar nav a svg {
    transition: all 0.2s ease;
}

/* hover icon */
.fi-sidebar nav a:hover svg {
    transform: scale(1.1);
}

/* active icon */
.fi-sidebar nav a[aria-current="page"] svg {
    color: #0284c7;
}

/* ===== GROUP LABEL ===== */
.fi-sidebar-group-label {
    font-size: 12px;
    font-weight: 600;
    color: #9ca3af;
    margin-left: 12px;
    margin-top: 12px;
}

/* ===== SCROLLBAR NICE ===== */
.fi-sidebar::-webkit-scrollbar {
    width: 6px;
}

.fi-sidebar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}

/* ===== CONTENT AREA ===== */
.fi-main {
    background: #f9fafb;
}

</style>'
            )
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
    public function boot(): void
    {
        FilamentAsset::register([
            Css::make('custom-styles', asset('css/filament.css')),
        ]);
    }
}
