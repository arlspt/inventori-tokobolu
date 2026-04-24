<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengadaanDetail extends Model
{
    protected $table = 'pengadaan_detail';

    protected $fillable = [
        'pengadaan_id',
        'bahan_baku_id',
        'jumlah',
        'harga',
        'subtotal'
    ];

    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
    // protected static function booted()
    // {
    //     static::saving(function ($detail) {

    //         $kg = $detail->jumlah; // input dari form (kg)

    //         // simpan ke database dalam gram
    //         $detail->jumlah = $kg * 1000;

    //         // hitung subtotal pakai kg
    //         $detail->subtotal = $kg * $detail->harga;
    //     });
    // }
}
