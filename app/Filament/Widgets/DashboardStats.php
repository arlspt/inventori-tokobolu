<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;

class DashboardStats extends StatsOverviewWidget
{
    public string $filterMode = 'mingguan';

    public ?int $minggu = 2;

    public ?int $bulan = null;

    #[On('filterUpdated')]
    public function updateFilter(
        $mode,
        $minggu = null,
        $bulan = null
    ) {
        $this->filterMode = $mode;

        $this->minggu = $minggu;

        $this->bulan = $bulan;
    }

    protected function getStats(): array
    {
        $range     = $this->getDateRange();
        $rangeLast = $this->getLastWeekRange();

        // ── PRODUKSI BERHASIL ──
        $produksiNow = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($range) {
            $q->whereBetween('tanggal', $range);
        })->sum(DB::raw('jumlah_produksi - gagal'));

        $produksiLast = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($rangeLast) {
            $q->whereBetween('tanggal', $rangeLast);
        })->sum(DB::raw('jumlah_produksi - gagal'));

        $produksiPercent = $produksiLast > 0
            ? (($produksiNow - $produksiLast) / $produksiLast) * 100
            : 0;

        $produksiChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day)->toDateString();
            return \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            })->sum(DB::raw('jumlah_produksi - gagal'));
        })->toArray();

        // ── PRODUK GAGAL ──
        $gagalNow = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($range) {
            $q->whereBetween('tanggal', $range);
        })->sum('gagal');

        $gagalLast = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($rangeLast) {
            $q->whereBetween('tanggal', $rangeLast);
        })->sum('gagal');

        $gagalPercent = $gagalLast > 0
            ? (($gagalNow - $gagalLast) / $gagalLast) * 100
            : 0;

        $gagalChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day)->toDateString();
            return \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($date) {
                $q->whereDate('tanggal', $date);
            })->sum('gagal');
        })->toArray();

        // ── DISTRIBUSI ──
        $distribusiNow = \App\Models\Distribusi::whereBetween('tanggal', $range)
            ->where('status', 'dikirim')
            ->count();

        $distribusiLast = \App\Models\Distribusi::whereBetween('tanggal', $rangeLast)
            ->where('status', 'dikirim')
            ->count();

        $distribusiPercent = $distribusiLast > 0
            ? (($distribusiNow - $distribusiLast) / $distribusiLast) * 100
            : 0;

        $distribusiChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day)->toDateString();
            return \App\Models\Distribusi::whereDate('tanggal', $date)
                ->where('status', 'dikirim')
                ->count();
        })->toArray();

        // ── RETUR ──
        $returNow = \App\Models\Retur::whereBetween('tanggal', $range)
            ->whereNull('deleted_at')
            ->count();

        $returLast = \App\Models\Retur::whereBetween('tanggal', $rangeLast)
            ->whereNull('deleted_at')
            ->count();

        $returPercent = $returLast > 0
            ? (($returNow - $returLast) / $returLast) * 100
            : 0;

        $returChart = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day)->toDateString();
            return \App\Models\Retur::whereDate('tanggal', $date)
                ->whereNull('deleted_at')
                ->count();
        })->toArray();

        return [
            Stat::make('Total Produksi', $produksiNow)
                ->description(number_format($produksiPercent, 1) . '% dari periode lalu')
                ->descriptionIcon($produksiPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($produksiPercent >= 0 ? 'success' : 'danger')
                ->chart($produksiChart),

            Stat::make('Produk Gagal', $gagalNow)
                ->description(number_format($gagalPercent, 1) . '% dari periode lalu')
                ->descriptionIcon($gagalPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($gagalPercent <= 0 ? 'success' : 'danger')
                ->chart($gagalChart),

            Stat::make('Distribusi', $distribusiNow)
                ->description(number_format($distribusiPercent, 1) . '% dari periode lalu')
                ->descriptionIcon($distribusiPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($distribusiPercent >= 0 ? 'success' : 'danger')
                ->chart($distribusiChart),

            Stat::make('Retur', $returNow)
                ->description(number_format($returPercent, 1) . '% dari periode lalu')
                ->descriptionIcon($returPercent >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($returPercent <= 0 ? 'success' : 'danger')
                ->chart($returChart),
        ];
    }

    // ── range periode yang dipilih ──
    private function getDateRange(): array
    {
        // HARIAN
        if ($this->filterMode === 'harian') {

            return [
                now()->toDateString(),
                now()->toDateString(),
            ];
        }

        // MINGGUAN
        if ($this->filterMode === 'mingguan') {

            $start =
                now()
                ->startOfMonth()
                ->addDays(
                    ($this->minggu - 1) * 7
                );

            $end =
                $start
                ->copy()
                ->addDays(6);

            return [
                $start->toDateString(),
                $end->toDateString(),
            ];
        }

        // BULANAN
        $date =
            now()
            ->month($this->bulan);

        return [

            $date
                ->startOfMonth()
                ->toDateString(),

            $date
                ->endOfMonth()
                ->toDateString(),

        ];
    }

    private function getLastWeekRange()
    {
        [
            $start,
            $end
        ] =
            $this->getDateRange();

        return [
            Carbon::parse($start)
                ->subWeek()
                ->toDateString(),

            Carbon::parse($end)
                ->subWeek()
                ->toDateString(),
        ];
    }
}
