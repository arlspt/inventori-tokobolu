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
    // protected static function booted()
    // {
    //     static::creating(function ($pengadaan) {
    //         $today = now()->format('Ymd');

    //         $count = self::whereDate('created_at', now()->toDateString())->count() + 1;

    //         $pengadaan->kode_pengadaan = 'PGD-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    //     });
    // }
}
