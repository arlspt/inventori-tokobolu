<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiDetail extends Model
{
    protected $table = 'produksi_detail';

    protected $fillable = [
        'produksi_id',
        'produk_id',
        'jumlah_produksi',
        'gagal'
    ];

    public function produksi()
    {
        return $this->belongsTo(Produksi::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    // protected static function booted()
    // {
    //     static::saving(function ($detail) {
    //         $detail->hasil_bersih = $detail->jumlah - $detail->gagal;
    //     });
    // }
}
