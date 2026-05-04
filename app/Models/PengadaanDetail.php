<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


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

    protected static function booted()
    {
        static::saving(function ($detail) {
            $detail->subtotal = $detail->jumlah * $detail->harga;
        });

        // CREATE
        static::created(function ($detail) {
            $bahan = \App\Models\BahanBaku::find($detail->bahan_baku_id);
            if ($bahan) {
                $bahan->increment('stok', $detail->jumlah);
            }
        });

        // UPDATE
        static::updating(function ($detail) {

            $bahan = \App\Models\BahanBaku::find($detail->bahan_baku_id);
            if (!$bahan) return;

            $oldJumlah = $detail->getOriginal('jumlah');
            $newJumlah = $detail->jumlah;

            $selisih = $newJumlah - $oldJumlah;

            $bahan->increment('stok', $selisih);

            $bahanLamaId = $detail->getOriginal('bahan_baku_id');
            $bahanBaruId = $detail->bahan_baku_id;

            if ($bahanLamaId != $bahanBaruId) {

                $bahanLama = \App\Models\BahanBaku::find($bahanLamaId);
                if ($bahanLama) {
                    $bahanLama->decrement('stok', $oldJumlah);
                }

                $bahanBaru = \App\Models\BahanBaku::find($bahanBaruId);
                if ($bahanBaru) {
                    $bahanBaru->increment('stok', $newJumlah);
                }

                return;
            }
        });

        // DELETE 🔥
        static::deleting(function ($detail) {

            $bahan = \App\Models\BahanBaku::find($detail->bahan_baku_id);

            if ($bahan) {
                $bahan->decrement('stok', $detail->jumlah);
            }
        });
    }
}
