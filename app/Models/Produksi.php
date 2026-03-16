<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produksi extends Model
{
    protected $table = 'produksi';

    protected $fillable = [
        'tanggal',
        'user_id',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function produksiDetail()
    {
        return $this->hasMany(ProduksiDetail::class);
    }
}
