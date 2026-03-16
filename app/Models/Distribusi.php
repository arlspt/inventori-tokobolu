<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Distribusi extends Model
{
    protected $table = 'distribusi';

    protected $fillable = [
        'tanggal',
        'reseller_id',
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

    public function distribusiDetail()
    {
        return $this->hasMany(DistribusiDetail::class);
    }

    public function retur()
    {
        return $this->hasMany(Retur::class);
    }
}
