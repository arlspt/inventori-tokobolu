<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribusi extends Model
{
    protected $table = 'distribusi';

    protected $fillable = [
        'tanggal',
        'reseller_id',
        'tujuan_lain',
        'user_id',
        'nomor_invoice',
        'total'
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(DistribusiDetail::class);
    }

    public function retur()
    {
        return $this->hasMany(Retur::class);
    }
    protected static function booted()
    {
        static::saved(function ($distribusi) {
            $distribusi->total = $distribusi->detail()->sum('subtotal');
            $distribusi->saveQuietly();
        });
    }
}
