<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    protected $table = 'reseller';

    protected $fillable = [
        'nama_reseller',
        'alamat',
        'no_telp',
        'kota',
    ];

    public function distribusi()
    {
        return $this->hasMany(Distribusi::class);
    }
}
