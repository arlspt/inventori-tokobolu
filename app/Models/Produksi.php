<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Produksi extends Model
{
    protected $table = 'produksi';

    protected $fillable = [
        'tanggal',
        'user_id',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produksiDetail()
    {
        return $this->hasMany(ProduksiDetail::class);
    }
    protected static function booted()
    {
        static::deleting(function ($produksi) {

            DB::transaction(function () use ($produksi) {

                $produksi->load('produksiDetail.produk.resep');

                foreach ($produksi->produksiDetail as $detail) {

                    $produk = $detail->produk;

                    if (!$produk) continue;

                    // 🔥 BALIKIN BAHAN
                    foreach ($produk->resep as $resep) {

                        $total = $resep->jumlah * $detail->jumlah_produksi;

                        $bahan = \App\Models\BahanBaku::find($resep->bahan_baku_id);

                        if ($bahan) {
                            $bahan->increment('stok', $total);
                        }
                    }

                    // 🔥 KURANGI STOK PRODUK
                    $hasilJadi = $detail->jumlah_produksi - $detail->gagal;

                    if ($hasilJadi > 0) {
                        $produk->decrement('stok', $hasilJadi);
                    }
                }
            });
        });
    }
}
