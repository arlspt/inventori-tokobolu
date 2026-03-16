<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';

    protected $fillable = [
        'nama_bahan',
        'satuan',
        'stok'
    ];

    public function pengadaanDetail()
    {
        return $this->hasMany(PengadaanDetail::class);
    }
}
