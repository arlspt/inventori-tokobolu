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

    public string $filterMode = 'mingguan';

    public ?int $minggu = 2;

    public ?int $bulan = null;

    #[On('filterUpdated')]
    public function updateFilter(
        $mode,
        $minggu = null,
        $bulan = null
    ): void {

        $this->filterMode = $mode;

        $this->minggu = $minggu;

        $this->bulan = $bulan;
    }

    protected function getData(): array
    {
        // =========================
        // HARIAN
        // =========================
        if ($this->filterMode === 'harian') {

            $today = now();

            $total =
                \App\Models\ProduksiDetail::whereHas(
                    'produksi',
                    fn($q) =>
                    $q->whereDate(
                        'tanggal',
                        $today
                    )
                )
                ->sum(
                    DB::raw(
                        'jumlah_produksi - gagal'
                    )
                );

            return [

                'datasets' => [[
                    'label' => 'Produksi',
                    'data' => [$total],
                ]],

                'labels' => [
                    'Hari Ini'
                ],

            ];
        }


        // =========================
        // MINGGUAN
        // =========================
        if ($this->filterMode === 'mingguan') {

            $bulan =
                $this->bulan
                ?: now()->month;

            $minggu =
                $this->minggu
                ?: 2;

            $start =
                Carbon::create(
                    now()->year,
                    $bulan,
                    1
                )
                ->addDays(
                    ($minggu - 1) * 7
                );

            $end =
                $start
                ->copy()
                ->addDays(6);

            $data =
                collect();

            while (
                $start <= $end
            ) {

                $total =
                    \App\Models\ProduksiDetail
                    ::whereHas(
                        'produksi',
                        fn($q) =>
                        $q->whereDate(
                            'tanggal',
                            $start
                        )
                    )
                    ->sum(
                        DB::raw(
                            'jumlah_produksi - gagal'
                        )
                    );

                $data->push([

                    'label' =>
                    $start
                        ->locale('id')
                        ->translatedFormat('D'),

                    'total' =>
                    $total,

                ]);

                $start->addDay();
            }

            return [

                'datasets' => [[
                    'label' => 'Produksi',
                    'data' =>
                    $data
                        ->pluck('total')
                        ->toArray(),
                ]],

                'labels' =>
                $data
                    ->pluck('label')
                    ->toArray(),

            ];
        }


        // =========================
        // BULANAN
        // =========================

        $bulan =
            $this->bulan
            ?: now()->month;

        $start =
            Carbon::create(
                now()->year,
                $bulan,
                1
            );

        $jumlahHari =
            $start
            ->copy()
            ->daysInMonth;

        $data =
            collect(range(
                1,
                $jumlahHari
            ))->map(
                function ($day)
                use ($bulan) {

                    $date =
                        Carbon::create(
                            now()->year,
                            $bulan,
                            $day
                        );

                    return [

                        'label' =>
                        $date
                            ->format('d'),

                        'total' =>

                        \App\Models\ProduksiDetail
                            ::whereHas(
                                'produksi',
                                fn($q) =>
                                $q
                                    ->whereDate(
                                        'tanggal',
                                        $date
                                    )
                            )
                            ->sum(
                                DB::raw(
                                    'jumlah_produksi-gagal'
                                )
                            )

                    ];
                }
            );

        return [

            'datasets' => [[
                'label' => 'Produksi',
                'data' =>
                $data
                    ->pluck('total')
                    ->toArray(),
            ]],

            'labels' =>
            $data
                ->pluck('label')
                ->toArray(),

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
