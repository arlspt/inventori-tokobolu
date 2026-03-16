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
}
