<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturDetail extends Model
{
    protected $table = 'retur_detail';

    protected $fillable = [
        'retur_id',
        'produk_id',
        'jumlah',
        'alasan'
    ];

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    protected static function booted()
    {
        // 🔥 saat create retur detail
        static::created(function ($detail) {

            $retur = $detail->retur;

            $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
                ->where('produk_id', $detail->produk_id)
                ->first();

            if ($distribusiDetail) {
                $distribusiDetail->jumlah -= $detail->jumlah;

                if ($distribusiDetail->jumlah < 0) {
                    $distribusiDetail->jumlah = 0;
                }

                $distribusiDetail->save();
            }
        });

        // 🔥 saat delete retur detail
        static::deleted(function ($detail) {

            $retur = $detail->retur;

            $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
                ->where('produk_id', $detail->produk_id)
                ->first();

            if ($distribusiDetail) {
                $distribusiDetail->jumlah += $detail->jumlah;
                $distribusiDetail->save();
            }
        });

        static::updating(function ($detail) {

            $retur = $detail->retur;

            $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
                ->where('produk_id', $detail->produk_id)
                ->first();

            if ($distribusiDetail) {
                // 🔥 balikin jumlah lama dulu
                $distribusiDetail->jumlah += $detail->getOriginal('jumlah');
                $distribusiDetail->save();
            }
        });

        static::updated(function ($detail) {
            $retur = $detail->retur;
            $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
                ->where('produk_id', $detail->produk_id)
                ->first();

            if ($distribusiDetail) {
                // 🔥 kurangi jumlah baru
                $distribusiDetail->jumlah -= $detail->jumlah;

                if ($distribusiDetail->jumlah < 0) {
                    $distribusiDetail->jumlah = 0;
                }

                $distribusiDetail->save();
            }
        });
    }
}
