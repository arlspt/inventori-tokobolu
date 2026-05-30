<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Livewire\Attributes\On;

class ProduksiChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Produksi';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 2;

    public ?string $filter = 'minggu_ini';

    #[On('filterUpdated')]
    public function updateFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    protected function getData(): array
    {
        $now = now();

        // ── HARI INI: 1 bar total ──
        if ($this->filter === 'hari_ini') {

            $total = \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($now) {
                $q->whereDate('tanggal', $now->toDateString());
            })->sum(DB::raw('jumlah_produksi - gagal'));

            $data = collect([[
                'label' => $now->locale('id')->translatedFormat('l, d M Y'),
                'total' => $total,
            ]]);
        }

        // ── MINGGU INI: per hari Senin–Minggu ──
        elseif ($this->filter === 'minggu_ini') {

            $start = $now->copy()->startOfWeek(Carbon::MONDAY);

            $data = collect(range(0, 6))->map(function ($i) use ($start) {
                $date = $start->copy()->addDays($i);
                return [
                    'label' => $date->locale('id')->translatedFormat('D'), // Sen, Sel, dst
                    'total' => \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($date) {
                        $q->whereDate('tanggal', $date->toDateString());
                    })->sum(DB::raw('jumlah_produksi - gagal')),
                ];
            });
        }

        // ── BULAN INI: per minggu dalam bulan ──
        elseif ($this->filter === 'bulan_ini') {

            $startOfMonth = $now->copy()->startOfMonth();
            $endOfMonth   = $now->copy()->endOfMonth();

            // hitung jumlah minggu dalam bulan ini
            $weeks = (int) ceil($endOfMonth->day / 7);

            $data = collect(range(0, $weeks - 1))->map(function ($i) use ($startOfMonth, $endOfMonth) {

                $start = $startOfMonth->copy()->addDays($i * 7);
                $end   = $start->copy()->addDays(6);

                // jangan melewati akhir bulan
                if ($end->gt($endOfMonth)) $end = $endOfMonth->copy();

                return [
                    'label' => 'Minggu ' . ($i + 1),
                    'total' => \App\Models\ProduksiDetail::whereHas('produksi', function ($q) use ($start, $end) {
                        $q->whereBetween('tanggal', [
                            $start->toDateString(),
                            $end->toDateString(),
                        ]);
                    })->sum(DB::raw('jumlah_produksi - gagal')),
                ];
            });
        }

        // ── FALLBACK ──
        else {
            $data = collect([['label' => '-', 'total' => 0]]);
        }

        return [
            'datasets' => [[
                'label' => 'Produksi Berhasil',
                'data'  => $data->pluck('total')->toArray(),
            ]],
            'labels' => $data->pluck('label')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'ticks' => [
                        'stepSize'  => 1,        // ✅ step selalu 1
                        'precision' => 0,         // ✅ tidak ada desimal
                    ],
                    'beginAtZero' => true,        // ✅ mulai dari 0
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,           // ✅ sembunyikan legend kalau mau lebih bersih
                ],
            ],
        ];
    }
}
