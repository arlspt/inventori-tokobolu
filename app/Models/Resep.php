<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 'resep';

    protected $fillable = [
        'produk_id',
        'bahan_baku_id',
        'jumlah',
        'versi',
        'berlaku_dari',
        'aktif',
    ];

    protected $casts = [
        'aktif'        => 'boolean',
        'berlaku_dari' => 'date',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
