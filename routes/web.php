<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReturController;

Route::get('/', function () {
    return view('welcome');
});
// Cetak invoice per-distribusi
Route::get('/invoice/{id}/cetak', [InvoiceController::class, 'cetak'])
    ->name('invoice.cetak')
    ->middleware(['auth']);

// Cetak rekap bulanan reseller
// Contoh: /invoice/rekap-bulanan?reseller_id=1&bulan=2025-05
Route::get('/invoice/rekap-bulanan', [InvoiceController::class, 'rekapBulanan'])
    ->name('invoice.rekap-bulanan')
    ->middleware(['auth']);

// Cetak per-retur
Route::get('/retur/{id}/cetak', [ReturController::class, 'cetak'])
    ->name('retur.cetak')
    ->middleware(['auth']);

// Rekap bulanan retur per reseller
// Contoh: /retur/rekap-bulanan?reseller_id=1&bulan=2025-05
Route::get('/retur/rekap-bulanan', [ReturController::class, 'rekapBulanan'])
    ->name('retur.rekap-bulanan')
    ->middleware(['auth']);
