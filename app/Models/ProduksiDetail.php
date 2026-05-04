<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
    protected static function booted()
    {
        // 🔥 CREATE
        static::created(function ($detail) {
            DB::transaction(function () use ($detail) {

                $produk = Produk::with('resep')->find($detail->produk_id);

                $hasilJadi = $detail->jumlah_produksi - $detail->gagal;

                // 🔻 KURANGI BAHAN (berdasarkan total produksi)
                foreach ($produk->resep as $resep) {
                    $total = $resep->jumlah * $detail->jumlah_produksi;

                    $bahan = BahanBaku::find($resep->bahan_baku_id);

                    if ($bahan->stok < $total) {
                        throw new \Exception("Stok {$bahan->nama_bahan} tidak cukup");
                    }

                    $bahan->decrement('stok', $total);
                }

                // 🔺 TAMBAH STOK PRODUK (HANYA YANG JADI)
                if ($hasilJadi > 0) {
                    $produk->increment('stok', $hasilJadi);
                }
            });
        });

        // 🔥 UPDATE
        static::updating(function ($detail) {
            DB::transaction(function () use ($detail) {

                $original = $detail->getOriginal();

                $oldQty   = $original['jumlah_produksi'];
                $newQty   = $detail->jumlah_produksi;

                $oldGagal = $original['gagal'];
                $newGagal = $detail->gagal;

                $produk = Produk::with('resep')->find($detail->produk_id);

                // 🔥 HITUNG DELTA BAHAN
                $deltaQty = $newQty - $oldQty;

                if ($deltaQty != 0) {
                    foreach ($produk->resep as $resep) {

                        $total = $resep->jumlah * abs($deltaQty);

                        $bahan = BahanBaku::find($resep->bahan_baku_id);

                        if ($deltaQty > 0) {
                            // 🔴 tambah produksi → kurangi bahan
                            if ($bahan->stok < $total) {
                                throw new \Exception("Stok {$bahan->nama_bahan} tidak cukup");
                            }

                            $bahan->decrement('stok', $total);
                        } else {
                            // 🟢 kurangi produksi → kembalikan bahan
                            $bahan->increment('stok', $total);
                        }
                    }
                }

                // 🔥 HITUNG DELTA PRODUK (INI KUNCI)
                $oldHasil = $oldQty - $oldGagal;
                $newHasil = $newQty - $newGagal;

                $deltaProduk = $newHasil - $oldHasil;

                if ($deltaProduk > 0) {
                    $produk->increment('stok', $deltaProduk);
                } elseif ($deltaProduk < 0) {
                    $produk->decrement('stok', abs($deltaProduk));
                }
            });
        });

        // 🔥 DELETE (WAJIB ROLLBACK)
        // static::deleting(function ($detail) {

        //     DB::transaction(function () use ($detail) {

        //         $produk = \App\Models\Produk::with('resep')->find($detail->produk_id);

        //         if (!$produk) return;

        //         // 🔥 BALIKIN BAHAN BAKU
        //         foreach ($produk->resep as $resep) {

        //             $total = $resep->jumlah * $detail->jumlah_produksi;

        //             $bahan = \App\Models\BahanBaku::find($resep->bahan_baku_id);

        //             if ($bahan) {
        //                 $bahan->increment('stok', $total);
        //             }
        //         }

        //         // 🔥 KURANGI STOK PRODUK (ingat gagal!)
        //         $hasilJadi = $detail->jumlah_produksi - $detail->gagal;

        //         if ($hasilJadi > 0) {
        //             $produk->decrement('stok', $hasilJadi);
        //         }
        //     });
        // });
    }
}
