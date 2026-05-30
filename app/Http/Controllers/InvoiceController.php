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
        $distribusi = Distribusi::with(['detail.produk', 'reseller'])
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

        $request->validate([
            'reseller_id' => 'required|exists:reseller,id',
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

        // ✅ pastikan setiap distribusi detail-nya ter-load dengan benar
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

        $distribusiList = Distribusi::with(['detail.produk'])
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
}
