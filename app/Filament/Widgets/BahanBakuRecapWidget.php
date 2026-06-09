<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\BahanBaku;
use App\Models\ProduksiDetail;
use App\Models\PengadaanDetail;
use App\Models\Resep;
use Carbon\Carbon;

class BahanBakuRecapWidget extends Widget
{
    protected static string $view = 'filament.widgets.bahan-baku-recap-widget';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 4;

    public string $bulanRecap = '';

    public function mount(): void
    {
        $this->bulanRecap = now()->format('Y-m');
    }

    public function formatStok(int|float $nilai, string $satuan): string
    {
        if ($satuan === 'gram' && $nilai >= 1000) {
            return number_format($nilai / 1000, 2, ',', '.') . ' Kg';
        }
        if ($satuan === 'ml' && $nilai >= 1000) {
            return number_format($nilai / 1000, 2, ',', '.') . ' L';
        }
        return number_format($nilai, 0, ',', '.') . ' ' . $satuan;
    }

    public function getBulanOptions(): array
    {
        $options = [];

        $options[now()->format('Y-m')] = 'Bulan Ini (' . now()->locale('id')->translatedFormat('F Y') . ')';

        \App\Models\Pengadaan::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan')
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

    /**
     * Recap per bahan baku untuk bulan yang dipilih:
     * - penggunaan (dari produksi × resep)
     * - pengadaan masuk
     * - stok tersedia (saat ini)
     * - total harga penggunaan (penggunaan × harga terakhir pengadaan)
     */
    public function getRecapData(): \Illuminate\Support\Collection
    {
        if (!$this->bulanRecap) return collect();

        $bulan     = Carbon::createFromFormat('Y-m', $this->bulanRecap);
        $startDate = $bulan->copy()->startOfMonth();
        $endDate   = $bulan->copy()->endOfMonth();

        return BahanBaku::orderBy('nama_bahan')->get()->map(function ($bahan) use ($startDate, $endDate) {

            // ── PENGGUNAAN dari produksi di bulan ini ──
            $penggunaan = Resep::where('bahan_baku_id', $bahan->id)
                ->get()
                ->sum(function ($resep) use ($startDate, $endDate) {
                    return ProduksiDetail::where('produk_id', $resep->produk_id)
                        ->whereHas('produksi', fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
                        ->sum('jumlah_produksi') * $resep->jumlah;
                });

            // ── PENGADAAN MASUK di bulan ini ──
            $pengadaanMasuk = PengadaanDetail::where('bahan_baku_id', $bahan->id)
                ->whereHas('pengadaan', fn($q) => $q->whereBetween('tanggal', [$startDate, $endDate]))
                ->sum('jumlah');

            // ── HARGA TERAKHIR dari pengadaan ──
            $hargaTerakhir = PengadaanDetail::where('bahan_baku_id', $bahan->id)
                ->join('pengadaan', 'pengadaan_detail.pengadaan_id', '=', 'pengadaan.id')
                ->orderBy('pengadaan.tanggal', 'desc')
                ->orderBy('pengadaan_detail.id', 'desc')
                ->value('pengadaan_detail.harga') ?? 0;

            // ── TOTAL HARGA PENGGUNAAN ──
            $totalHargaPenggunaan = $penggunaan * $hargaTerakhir;

            return [
                'nama'                  => $bahan->nama_bahan,
                'satuan'                => $bahan->satuan,
                'penggunaan_raw'        => $penggunaan,
                'penggunaan_label'      => $this->formatStok($penggunaan, $bahan->satuan),
                'pengadaan_raw'         => $pengadaanMasuk,
                'pengadaan_label'       => $this->formatStok($pengadaanMasuk, $bahan->satuan),
                'stok_tersedia_raw'     => $bahan->stok,
                'stok_tersedia_label'   => $this->formatStok($bahan->stok, $bahan->satuan),
                'harga_satuan'          => $hargaTerakhir,
                'total_harga'           => $totalHargaPenggunaan,
            ];
        })->filter(fn($item) => $item['penggunaan_raw'] > 0 || $item['pengadaan_raw'] > 0);
    }

    public function getTotalHarga(): float
    {
        return $this->getRecapData()->sum('total_harga');
    }
}
