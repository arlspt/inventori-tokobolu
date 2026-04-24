<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';

    protected $fillable = [
        'nama_produk',
        'harga',
        'stok'
    ];

    public function produksiDetail()
    {
        return $this->hasMany(ProduksiDetail::class);
    }

    public function distribusiDetail()
    {
        return $this->hasMany(DistribusiDetail::class);
    }

    public function returDetail()
    {
        return $this->hasMany(ReturDetail::class);
    }
    public function resep()
    {
        return $this->hasMany(Resep::class);
    }
}
