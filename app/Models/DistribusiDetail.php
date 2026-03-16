<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DistribusiDetail extends Model
{
    protected $table = 'distribusi_detail';

    protected $fillable = [
        'distribusi_id',
        'produk_id',
        'jumlah',
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
}
