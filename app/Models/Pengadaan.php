<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $table = 'pengadaan';

    protected $fillable = [
        'tanggal',
        'user_id',
        'keterangan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pengadaanDetail()
    {
        return $this->hasMany(PengadaanDetail::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
