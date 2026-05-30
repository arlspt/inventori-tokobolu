<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\DB;

class ReturDetail extends Model
{
    protected $table = 'retur_detail';
    protected $fillable = [
        'retur_id',
        'produk_id',
        'jumlah',
        'alasan',
        'alasan_lain',
    ];

    public function retur()
    {
        return $this->belongsTo(Retur::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
