<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;


class DashboardStats extends StatsOverviewWidget
{
    public $filter = 'minggu_ini';

    #[On('filterUpdated')]
    public function updateFilter($filter)
    {
        $this->filter = $filter;
    }

    protected function getStats(): array
    {
        $range = $this->getDateRange();

        // 🔥 PRODUKSI BERHASIL
        $produksiNow = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($range) {
            $q->whereBetween('tanggal', $range);
        })->sum(DB::raw('jumlah_produksi - gagal'));

        $produksiLast = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) {
            $q->whereBetween('tanggal', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ]);
        })->sum(DB::raw('jumlah_produksi - gagal'));

        $produksiPercent = $produksiLast > 0
            ? (($produksiNow - $produksiLast) / $produksiLast) * 100
            : 0;

        $produksiChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day);
            return \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            })->sum(DB::raw('jumlah_produksi - gagal'));
        })->toArray();


        // ❌ PRODUK GAGAL
        $gagalNow = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($range) {
            $q->whereBetween('tanggal', $range);
        })->sum('gagal');

        $gagalLast = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) {
            $q->whereBetween('tanggal', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ]);
        })->sum('gagal');

        $gagalPercent = $gagalLast > 0
            ? (($gagalNow - $gagalLast) / $gagalLast) * 100
            : 0;

        $gagalChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day);
            return \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            })->sum('gagal');
        })->toArray();


        // 🚚 DISTRIBUSI (TRANSAKSI)
        $distribusiNow = \App\Models\Distribusi::whereBetween('tanggal', $range)
            ->where('status', 'dikirim')
            ->count();

        $distribusiLast = \App\Models\Distribusi::whereBetween('tanggal', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])
            ->where('status', 'dikirim')
            ->count();

        $distribusiPercent = $distribusiLast > 0
            ? (($distribusiNow - $distribusiLast) / $distribusiLast) * 100
            : 0;

        $distribusiChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day);
            return \App\Models\Distribusi::whereDate('tanggal', $date)
                ->where('status', 'dikirim')
                ->count();
        })->toArray();


        // 🔁 RETUR (TRANSAKSI)
        $returNow = \App\Models\Retur::whereBetween('tanggal', $range)
            ->whereNull('deleted_at')
            ->count();

        $returLast = \App\Models\Retur::whereBetween('tanggal', [
            now()->subWeek()->startOfWeek(),
            now()->subWeek()->endOfWeek()
        ])
            ->whereNull('deleted_at')
            ->count();

        $returPercent = $returLast > 0
            ? (($returNow - $returLast) / $returLast) * 100
            : 0;

        $returChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day);
            return \App\Models\Retur::whereDate('tanggal', $date)
                ->whereNull('deleted_at')
                ->count();
        })->toArray();


        // 🎯 RETURN FINAL CARD
        return [

            // ✅ PRODUKSI
            Stat::make('Total Produksi', $produksiNow)
                ->description(number_format($produksiPercent, 1) . '% dari minggu lalu')
                ->descriptionIcon($produksiPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($produksiPercent >= 0 ? 'success' : 'danger')
                ->chart($produksiChart),

            // ❌ GAGAL
            Stat::make('Produk Gagal', $gagalNow)
                ->description(number_format($gagalPercent, 1) . '% dari minggu lalu')
                ->descriptionIcon($gagalPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($gagalPercent <= 0 ? 'success' : 'danger')
                ->chart($gagalChart),

            // 🚚 DISTRIBUSI
            Stat::make('Distribusi', $distribusiNow)
                ->description(number_format($distribusiPercent, 1) . '% dari minggu lalu')
                ->descriptionIcon($distribusiPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($distribusiPercent >= 0 ? 'success' : 'danger')
                ->chart($distribusiChart),

            // 🔁 RETUR
            Stat::make('Retur', $returNow)
                ->description(number_format($returPercent, 1) . '% dari minggu lalu')
                ->descriptionIcon($returPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($returPercent <= 0 ? 'success' : 'danger')
                ->chart($returChart),
        ];
    }

    private function getDateRange()
    {
        return match ($this->filter) {
            'hari_ini' => [now()->startOfDay(), now()->endOfDay()],
            'bulan_ini' => [now()->startOfMonth(), now()->endOfMonth()],
            default => [
                now()->startOfWeek(Carbon::MONDAY),
                now()->endOfWeek(Carbon::SUNDAY),
            ],
        };
    }
}
