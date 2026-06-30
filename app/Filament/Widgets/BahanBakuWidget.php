<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\BahanBaku;
use App\Models\Pengadaan;
use Carbon\Carbon;
use Livewire\Attributes\On;

class BahanBakuWidget extends Widget
{
    protected static string $view = 'filament.widgets.bahan-baku-widget';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 3;

    // filter bulan history — terpisah dari filter global
    public string $bulanHistory = '';

    // kontrol popup
    public bool $showModal = false;

    public function mount(): void
    {
        $this->bulanHistory = now()->format('Y-m');
    }

    // ── helpers ──

    /**
     * Format stok: gram→Kg, ml→L kalau >= 1000
     */
    public function formatStok(int|float $stok, string $satuan): string
    {
        if ($satuan === 'gram' && $stok >= 1000) {
            return number_format($stok / 1000, 2, ',', '.') . ' Kg';
        }

        if ($satuan === 'ml' && $stok >= 1000) {
            return number_format($stok / 1000, 2, ',', '.') . ' L';
        }

        return number_format($stok, 0, ',', '.') . ' ' . $satuan;
    }

    /**
     * Stok bahan baku SAAT INI (langsung dari kolom stok)
     */
    public function getStokSaatIni(): \Illuminate\Support\Collection
    {
        return BahanBaku::orderBy('nama_bahan')->get()->map(function ($bahan) {
            return [
                'id'          => $bahan->id,
                'nama'        => $bahan->nama_bahan,
                'stok_raw'    => $bahan->stok,
                'satuan'      => $bahan->satuan,
                'stok_label'  => $this->formatStok($bahan->stok, $bahan->satuan),
                'status' => $bahan->stok === 0 ? 'habis' : ($bahan->stok < 3000 ? 'menipis' : 'aman'),
                'low'    => $bahan->stok < 3000,
            ];
        });
    }

    /**
     * Stok bahan baku berdasarkan bulan history yang dipilih
     * Dihitung dari: stok saat ini + penggunaan produksi sesudah bulan itu - pengadaan sesudah bulan itu
     */
    public function getStokHistory(): \Illuminate\Support\Collection
    {
        if (!$this->bulanHistory) {
            return $this->getStokSaatIni();
        }

        $endOfBulan = Carbon::createFromFormat('Y-m', $this->bulanHistory)->endOfMonth();

        // kalau bulan yang dipilih adalah bulan ini atau masa depan → tampilkan stok saat ini
        if ($endOfBulan->gte(now()->endOfMonth())) {
            return $this->getStokSaatIni();
        }

        return BahanBaku::orderBy('nama_bahan')->get()->map(function ($bahan) use ($endOfBulan) {

            $stokSekarang = $bahan->stok;

            // pengadaan yang masuk SETELAH akhir bulan yang dipilih
            $pengadaanSesudah = \App\Models\PengadaanDetail::where('bahan_baku_id', $bahan->id)
                ->whereHas('pengadaan', fn($q) => $q->where('tanggal', '>', $endOfBulan))
                ->sum('jumlah');

            // penggunaan produksi SETELAH akhir bulan yang dipilih
            $penggunaanSesudah = \App\Models\Resep::where('bahan_baku_id', $bahan->id)
                ->get()
                ->sum(function ($resep) use ($endOfBulan) {
                    return \App\Models\ProduksiDetail::where('produk_id', $resep->produk_id)
                        ->whereHas('produksi', fn($q) => $q->where('tanggal', '>', $endOfBulan))
                        ->sum('jumlah_produksi') * $resep->jumlah;
                });

            // stok di akhir bulan = stok sekarang - pengadaan sesudah + penggunaan sesudah
            $stokPadaBulan = $stokSekarang - $pengadaanSesudah + $penggunaanSesudah;

            if ($stokPadaBulan < 0) $stokPadaBulan = 0;

            return [
                'id'         => $bahan->id,
                'nama'       => $bahan->nama_bahan,
                'stok_raw'   => $stokPadaBulan,
                'satuan'     => $bahan->satuan,
                'stok_label' => $this->formatStok($stokPadaBulan, $bahan->satuan),
                'status' => $bahan->stok === 0 ? 'habis' : ($bahan->stok < 3000 ? 'menipis' : 'aman'),
                'low'    => $bahan->stok < 3000,
            ];
        });
    }

    /**
     * Daftar bulan yang tersedia (dari pengadaan)
     */
    public function getBulanOptions(): array
    {
        $options = [];

        // bulan ini selalu ada
        $options[now()->format('Y-m')] = 'Bulan Ini (' . now()->locale('id')->translatedFormat('F Y') . ')';

        // bulan dari data pengadaan
        Pengadaan::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan')
            ->distinct()
            ->orderByRaw('bulan DESC')
            ->pluck('bulan')
            ->each(function ($bulan) use (&$options) {
                if (!isset($options[$bulan])) {
                    $options[$bulan] = Carbon::createFromFormat('Y-m', $bulan)
                        ->locale('id')
                        ->translatedFormat('F Y');
                }
            });

        return $options;
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }
}