<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    protected $table = 'bahan_baku';
    protected $attributes = [
        'satuan' => 'gram',
    ];

    protected $fillable = [
        'nama_bahan',
        'stok',
        'satuan',
    ];

    public function pengadaanDetail()
    {
        return $this->hasMany(PengadaanDetail::class);
    }
}
