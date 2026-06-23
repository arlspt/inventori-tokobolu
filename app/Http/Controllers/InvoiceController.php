<?php

namespace App\Http\Controllers;

use App\Models\Distribusi;
use App\Models\Reseller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Cetak invoice per-distribusi
     */
    public function cetak($id)
    {
        $distribusi = Distribusi::with(['detail.produk', 'reseller', 'retur' => fn($q) => $q->withTrashed(), 'retur.detail',])
            ->where('status', '!=', 'dibatalkan')
            ->findOrFail($id);

        return view('invoice.invoice-distribusi', compact('distribusi'));
    }

    /**
     * Cetak rekap bulanan reseller
     * Query params: reseller_id, bulan (Y-m), contoh: 2025-05
     */
    public function rekapBulanan(Request $request)
    {
        // cek tipe tujuan
        if ($request->tipe_tujuan === 'tujuan_lain') {
            return $this->rekapBulananTujuanLain($request);
        }
        // jika reseller_id = all, maka tampilkan semua reseller
        if ($request->reseller_id === 'all') {
            return $this->rekapSemuaReseller($request);
        }

        $request->validate([
            'reseller_id' => 'required',
            'bulan'       => 'required|date_format:Y-m',
        ]);

        $reseller = Reseller::findOrFail($request->reseller_id);

        $bulan      = Carbon::createFromFormat('Y-m', $request->bulan);
        $bulanLabel = $bulan->locale('id')->translatedFormat('F Y');

        $distribusiList = Distribusi::with(['detail' => function ($q) {
            $q->with('produk'); // ✅ eager load produk di dalam detail
        }])
            ->where('reseller_id', $reseller->id)
            ->where('status', '!=', 'dibatalkan')
            ->whereYear('tanggal', $bulan->year)
            ->whereMonth('tanggal', $bulan->month)
            ->orderBy('tanggal')
            ->get();

        if ($distribusiList->isEmpty()) {
            abort(404, 'Tidak ada distribusi untuk reseller dan bulan yang dipilih.');
        }

        // pastikan setiap distribusi detail-nya ter-load dengan benar
        $distribusiList->each(function ($distribusi) {
            $distribusi->setRelation('detail', $distribusi->detail->values());
        });

        return view('invoice.invoice-rekap-bulanan', compact(
            'reseller',
            'distribusiList',
            'bulanLabel'
        ));
    }
    private function rekapBulananTujuanLain(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m',
        ]);

        $bulan      = Carbon::createFromFormat('Y-m', $request->bulan);
        $bulanLabel = $bulan->locale('id')->translatedFormat('F Y');

        $distribusiList = Distribusi::with([
            'detail.produk',
            'retur' => fn($q) => $q->withTrashed(),
            'retur.detail',
        ])
            ->whereNotNull('tujuan_lain')
            ->where('status', '!=', 'dibatalkan')
            ->whereYear('tanggal', $bulan->year)
            ->whereMonth('tanggal', $bulan->month)
            ->orderBy('tanggal')
            ->get();

        if ($distribusiList->isEmpty()) {
            abort(404, 'Tidak ada distribusi tujuan lain di bulan yang dipilih.');
        }

        // pakai blade yang sama tapi tanpa reseller
        return view('invoice.invoice-rekap-tujuan-lain', compact(
            'distribusiList',
            'bulanLabel'
        ));
    }
    private function rekapSemuaReseller(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m',
        ]);

        $bulan      = Carbon::createFromFormat('Y-m', $request->bulan);
        $bulanLabel = $bulan->locale('id')->translatedFormat('F Y');

        $rekap = Distribusi::with([
            'reseller',
            'detail',
            'retur' => fn($q) => $q->withTrashed(), // ✅ tambahkan
            'retur.detail',                           // ✅ tambahkan
        ])
            ->whereNotNull('reseller_id')
            ->where('status', '!=', 'dibatalkan')
            ->whereYear('tanggal', $bulan->year)
            ->whereMonth('tanggal', $bulan->month)
            ->get()
            ->groupBy('reseller_id')
            ->map(function ($items) {
                $reseller     = $items->first()->reseller;
                $totalInvoice = $items->count();

                $totalQty = $items->sum(fn($d) => $d->detail->sum('jumlah'));

                // ✅ hitung total qty retur per reseller
                $totalQtyRetur = $items->sum(function ($d) {
                    return $d->retur->whereNull('deleted_at')
                        ->flatMap(fn($r) => $r->detail)
                        ->sum('jumlah');
                });

                // ✅ hitung total harga setelah dikurangi retur
                $totalHarga = $items->sum(function ($d) {
                    $returNominal = $d->retur->whereNull('deleted_at')
                        ->flatMap(fn($r) => $r->detail)
                        ->groupBy('produk_id')
                        ->map(fn($rd) => $rd->sum('jumlah'))
                        ->reduce(function ($carry, $qty, $produkId) use ($d) {
                            $harga = $d->detail->firstWhere('produk_id', $produkId)?->harga ?? 0;
                            return $carry + ($qty * $harga);
                        }, 0);
                    return max(0, $d->detail->sum('subtotal') - $returNominal);
                });

                return [
                    'nama_reseller'  => $reseller->nama_reseller,
                    'total_invoice'  => $totalInvoice,
                    'total_qty'      => $totalQty,
                    'total_qty_retur' => $totalQtyRetur, // ✅ tambahkan
                    'total_harga'    => $totalHarga,
                ];
            })
            ->sortByDesc('total_harga')
            ->values();

        return view('invoice.invoice-rekap-semua-reseller', compact('rekap', 'bulanLabel'));
    }
}