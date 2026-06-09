<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $title = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\DashboardFilter::class,
            \App\Filament\Widgets\DashboardStats::class,

            // ✅ Chart produksi (kiri) + Stok bahan baku (kanan) — sejajar 2 kolom
            \App\Filament\Widgets\ProduksiChart::class,
            \App\Filament\Widgets\BahanBakuWidget::class,

            // ✅ Recap bulanan bahan baku — full width
            \App\Filament\Widgets\BahanBakuRecapWidget::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2; // 2 kolom agar chart dan bahan baku sejajar
    }
}
