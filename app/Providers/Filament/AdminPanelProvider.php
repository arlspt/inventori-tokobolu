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
            ->brandName('Bolu Legenda Malang')        // ganti teks Laravel
            ->brandLogo(asset('images/logo_header_hitam.png'))     // tampilkan logo
            ->darkModeBrandLogo(asset('images/logo_header_putih.png')) // logo untuk dark mode
            ->brandLogoHeight('42px')                  // ukuran logo
            ->favicon(asset('images/logo.jpeg')) // logo di tab browser
            ->colors([
                'primary' => Color::hex('#16a34a'), // Ubah warna utama di sini
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
                // DashboardFilter::class,
                // DashboardStats::class,
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
    background: #ffffff ;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
.dark .fi-sidebar {
    background: #111C18;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

/* ===== KECILKAN MARGIN TABEL ===== */
    .fi-ta-text {
    padding-top: 0.15rem !important;
    padding-bottom: 0.15rem !important;
}

// ===== TOPBAR =====
.dark .fi-topbar {
    background: #111C18 !important;
    border-bottom: 1px solid #294237;
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
.dark .fi-sidebar nav a {
    color: #D1D5DB !important;
}

/* ===== HOVER EFFECT ===== */
.fi-sidebar nav a:hover {
    background: #f1f5f9 !important;
    transform: translateX(3px);
}
.dark .fi-sidebar nav a:hover {
    background: #1D2D26 !important;
    color: #294237 !important;
}

/* ===== ACTIVE MENU ===== */
.fi-sidebar nav a[aria-current="page"] {
    background: linear-gradient(90deg, #e0f2fe, #f0f9ff) !important;
    color: #0284c7 !important;
    font-weight: 600;
    position: relative;
}
.dark .fi-sidebar nav a[aria-current="page"] {
    background: linear-gradient(
        90deg,
        rgba(34,197,94,.18),
        rgba(34,197,94,.08)
    ) !important;

    color: #4ADE80 !important;
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
background: #22C55E;}

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
.dark .fi-sidebar nav a svg {
    color: #9CA3AF;
}

.dark .fi-sidebar nav a:hover svg {
    color: #F3F4F6;
}

.dark .fi-sidebar nav a[aria-current="page"] svg {
    color: #4ADE80;
}

/* ===== GROUP LABEL ===== */
.fi-sidebar-group-label {
    font-size: 12px;
    font-weight: 600;
    color: #9ca3af;
    margin-left: 12px;
    margin-top: 12px;
}
.dark .fi-sidebar-group-label {
    color: #6B7280;
}

/* ===== SCROLLBAR NICE ===== */
.fi-sidebar::-webkit-scrollbar {
    width: 6px;
}

.fi-sidebar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}
.dark .fi-sidebar::-webkit-scrollbar-thumb {
    background: #294237;
}

/* ===== BACKGROUND LOGIN PAGE ===== */
.fi-simple-layout {
    background-color: #F3FAF7;
}

.fi-simple-main {
    background-color: #ffffff;
}

.dark .fi-simple-layout {
    background-color: #0F1A16;
}

.dark .fi-simple-main {
    background-color: #16231E;
    border: 1px solid #294237;
}

/* ===== CONTENT AREA ===== */
.fi-main {
background: #F3FAF7 ; /* hijau soft sangat muda */
}
.dark .fi-main {
    background: #0F1A16;
}
   /* ===== SEMBUNYIKAN TEKS LARAVEL ===== */
// div.fi-logo {
//     font-size: 0 !important;
// }
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