<?php

namespace App\Http\Controllers;

use App\Models\Retur;
use App\Models\Reseller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    /**
     * Cetak per-retur
     */
    public function cetak($id)
    {
        $retur = Retur::with(['detail.produk', 'distribusi.reseller'])
            ->withTrashed()
            ->findOrFail($id);

        return view('retur.cetak-retur', compact('retur'));
    }

    /**
     * Router rekap bulanan — arahkan ke reseller atau tujuan lain
     */
    public function rekapBulanan(Request $request)
    {
        if ($request->tipe_tujuan === 'tujuan_lain') {
            return $this->rekapBulananTujuanLain($request);
        }

        return $this->rekapBulananReseller($request);
    }

    /**
     * Rekap bulanan retur per reseller
     */
    private function rekapBulananReseller(Request $request)
    {
        $request->validate([
            'reseller_id' => 'required|exists:reseller,id',
            'bulan'       => 'required|date_format:Y-m',
        ]);

        $reseller   = Reseller::findOrFail($request->reseller_id);
        $bulan      = Carbon::createFromFormat('Y-m', $request->bulan);
        $bulanLabel = $bulan->locale('id')->translatedFormat('F Y');

        $returList = Retur::with(['detail.produk', 'distribusi'])
            ->whereNull('deleted_at')
            ->whereHas('distribusi', fn($q) => $q->where('reseller_id', $reseller->id))
            ->whereYear('tanggal', $bulan->year)
            ->whereMonth('tanggal', $bulan->month)
            ->orderBy('tanggal')
            ->get();

        if ($returList->isEmpty()) {
            abort(404, 'Tidak ada retur untuk reseller dan bulan yang dipilih.');
        }

        return view('retur.rekap-retur-bulanan', compact(
            'reseller',
            'returList',
            'bulanLabel'
        ));
    }

    /**
     * Rekap bulanan retur tujuan lain (semua tujuan_lain)
     */
    private function rekapBulananTujuanLain(Request $request)
    {
        $request->validate([
            'bulan' => 'required|date_format:Y-m',
        ]);

        $bulan      = Carbon::createFromFormat('Y-m', $request->bulan);
        $bulanLabel = $bulan->locale('id')->translatedFormat('F Y');

        $returList = Retur::with(['detail.produk', 'distribusi'])
            ->whereNull('deleted_at')
            ->whereHas('distribusi', fn($q) => $q->whereNotNull('tujuan_lain'))
            ->whereYear('tanggal', $bulan->year)
            ->whereMonth('tanggal', $bulan->month)
            ->orderBy('tanggal')
            ->get();

        if ($returList->isEmpty()) {
            abort(404, 'Tidak ada retur tujuan lain di bulan yang dipilih.');
        }

        return view('retur.rekap-retur-tujuan-lain', compact(
            'returList',
            'bulanLabel'
        ));
    }
}
