<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'nama_supplier',
        'telepon',
        'alamat'
    ];

    public function pengadaan()
    {
        return $this->hasMany(Pengadaan::class);
    }
}
