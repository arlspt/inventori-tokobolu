<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $table = 'pengadaan';

    protected $fillable = [
        'tanggal',
        'user_id',
        'supplier_id',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengadaanDetail()
    {
        return $this->hasMany(PengadaanDetail::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    protected static function booted()
    {
        static::deleting(function ($pengadaan) {

            // 🔥 WAJIB: load relasi dulu
            $pengadaan->load('pengadaanDetail');

            foreach ($pengadaan->pengadaanDetail as $detail) {

                $bahan = \App\Models\BahanBaku::find($detail->bahan_baku_id);

                if ($bahan) {
                    $bahan->decrement('stok', $detail->jumlah);
                }
            }
        });
    }
}
