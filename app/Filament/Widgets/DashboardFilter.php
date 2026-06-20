<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardFilter extends Widget
{
    protected static string $view =
    'filament.widgets.dashboard-filter';

    protected int|string|array $columnSpan = 'full';

    public string $filterMode = 'mingguan';

    public int $minggu = 2;

    public int $bulan;

    public function mount(): void
    {
        $this->bulan = now()->month;

        $this->dispatch(
            'filterUpdated',
            mode: 'mingguan',
            minggu: $this->minggu,
            bulan: $this->bulan
        );
    }

    public function setMode(string $mode): void
    {
        $this->filterMode = $mode;

        // HARUS LANGSUNG UPDATE
        if ($mode === 'harian') {

            $this->dispatch(
                'filterUpdated',
                mode: 'harian',
                minggu: null,
                bulan: null
            );
        }
    }

    public function applyMinggu(): void
    {
        $this->filterMode = 'mingguan';

        $this->dispatch(
            'filterUpdated',
            mode: 'mingguan',
            minggu: $this->minggu,
            bulan: null
        );
    }

    public function applyBulan(): void
    {
        $this->filterMode = 'bulanan';

        $this->dispatch(
            'filterUpdated',
            mode: 'bulanan',
            minggu: null,
            bulan: $this->bulan
        );
    }
}
