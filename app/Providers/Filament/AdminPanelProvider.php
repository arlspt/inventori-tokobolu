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
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Auth\Login::class)
            ->passwordReset()
            ->unsavedChangesAlerts()
            ->brandName('Bolu Legenda Malang')        // ganti teks Laravel
            ->brandLogo(asset('images/logo_header_hitam.png'))     // tampilkan logo
            ->darkModeBrandLogo(asset('images/logo_header_putih.png')) // logo untuk dark mode
            ->brandLogoHeight('42px')                  // ukuran logo
            ->favicon(asset('images/logo_header_hitam.jpeg')) // logo di tab browser
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
    background:
        radial-gradient(circle at top left, #dcfce7 0%, #f3faf7 45%, #ffffff 100%);
    min-height: 100vh;
}

.fi-simple-layout .fi-logo {
    display: none !important;
}

.fi-simple-main {
    background: rgba(255,255,255,.92);
    backdrop-filter: blur(10px);
    border-radius: 24px;
    padding: 2rem !important;
    box-shadow:
        0 20px 40px rgba(0,0,0,.08),
        0 4px 10px rgba(0,0,0,.04);
    border: 1px solid #e5e7eb;
}

.dark .fi-simple-layout {
    background:
        radial-gradient(circle at top left, #1b2e25 0%, #0f1a16 45%, #08100d 100%);
}
.dark .fi-simple-layout .fi-logo {
    display: none !important;
}

.dark .fi-simple-main {
    background-color: #16231E;
    border: 1px solid #294237;
}

/* ===== SEMBUNYIKAN HEADING SIGN IN BAWAAN ===== */
.fi-simple-page > .fi-simple-page-heading {
    display: none !important;
}

/* ===== UNTUK COVER SEMUA KEMUNGKINAN SELECTOR ===== */
.fi-simple-layout h1,
.fi-simple-layout h2 {
    display: none !important;
}

/* ===== LOGIN HEADER ===== */

.custom-login-header{
    text-align:center;
    margin-bottom:24px;
}

.custom-login-title{
    font-size:28px;
    font-weight:800;
    letter-spacing:-0.5px;
    color:#111827;
}
.custom-login-subtitle{
    margin-top:6px;
    font-size:13px;
    color:#6b7280;
}

/* logo */

.login-logo-light,
.login-logo-dark{
    height:110px;
    width:auto;
    display:block;
    margin:0 auto 12px;
    transition:.3s ease;
}

.login-logo-dark{
    display:none;
}

/* ===== DARK MODE ===== */

.dark .login-logo-light{
    display:none;
}

.dark .login-logo-dark{
    display:block;
}

.dark .custom-login-title{
    color:#ffffff;
}
.dark .custom-login-subtitle{
    color:#9ca3af;
}

/* ===== INPUT ===== */

.fi-input,
.fi-select-input {
    border-radius: 12px !important;
}

.fi-input:focus,
.fi-select-input:focus {
    border-color: #22c55e !important;
    box-shadow:
        0 0 0 3px rgba(34,197,94,.15) !important;
}

/* Modal dashboard bahan baku */
.stock-modal-overlay{
    position: fixed !important;
    inset: 0 !important;
    z-index: 9998 !important;
}

.stock-modal-content{
    position: relative !important;
    z-index: 9999 !important;
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
//     display: none !important;
// }
    </style>'
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn(): string => Blade::render('
        <div class="custom-login-header">

            <img
                src="' . asset('images/logo_login_hitam.png') . '"
                class="login-logo-light"
                alt="Logo"
            >

            <img
                src="' . asset('images/logo_login_putih.png') . '"
                class="login-logo-dark"
                alt="Logo"
            >

            <div class="custom-login-title">
                Sign In
            </div>

            <div class="custom-login-subtitle">
                Inventori & Distribusi Bolu Legenda Malang
            </div>

        </div>
    ')
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