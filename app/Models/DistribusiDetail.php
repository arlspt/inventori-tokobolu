<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DistribusiDetail extends Model
{
    protected $table = 'distribusi_detail';
    protected $fillable = [
        'distribusi_id',
        'produk_id',
        'jumlah',
        'jumlah_awal',
        'harga',
        'subtotal'
    ];
    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class);
    }
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    protected static function booted()
    {
        // 🔥 SAAT CREATING (SEBELUM INSERT)
        static::creating(function ($detail) {

            // ✅ SET jumlah_awal SEKALI SAJA (PALING PENTING)
            if (!$detail->jumlah_awal || $detail->jumlah_awal == 0) {
                $detail->jumlah_awal = $detail->jumlah;
            }
        });

        // 🔥 SAAT CREATED (SETELAH INSERT)
        static::created(function ($detail) {

            DB::transaction(function () use ($detail) {

                $produk = \App\Models\Produk::find($detail->produk_id);

                if (!$produk) {
                    throw new \Exception("Produk tidak ditemukan");
                }

                // 🔴 VALIDASI STOK
                if ($produk->stok < $detail->jumlah) {
                    throw new \Exception("Stok tidak cukup untuk distribusi");
                }

                // 🔻 KURANGI STOK
                $produk->decrement('stok', $detail->jumlah);
            });
        });

        // 🔥 SAAT UPDATE
        static::updating(function ($detail) {

            DB::transaction(function () use ($detail) {

                // 🔒 LOCK jumlah_awal (TIDAK BOLEH BERUBAH)
                if ($detail->isDirty('jumlah_awal')) {
                    $detail->jumlah_awal = $detail->getOriginal('jumlah_awal');
                }

                $original = $detail->getOriginal();

                $oldQty = $original['jumlah'];
                $newQty = $detail->jumlah;

                $delta = $newQty - $oldQty;

                if ($delta == 0) return;

                $produk = \App\Models\Produk::find($detail->produk_id);

                if (!$produk) {
                    throw new \Exception("Produk tidak ditemukan");
                }

                if ($delta > 0) {
                    // 🔴 TAMBAH DISTRIBUSI → KURANGI STOK
                    if ($produk->stok < $delta) {
                        throw new \Exception("Stok tidak cukup");
                    }

                    $produk->decrement('stok', $delta);
                } else {
                    // 🟢 KURANGI DISTRIBUSI → KEMBALIKAN STOK
                    $produk->increment('stok', abs($delta));
                }
            });
        });

        // 🔥 SAAT DELETE
        static::deleting(function ($detail) {

            DB::transaction(function () use ($detail) {

                $produk = \App\Models\Produk::find($detail->produk_id);

                if (!$produk) return;

                // 🔄 KEMBALIKAN STOK
                $produk->increment('stok', $detail->jumlah);
            });
        });
    }
}
