<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $table = 'retur';

    protected $fillable = [
        'distribusi_id',
        'tanggal',
        'user_id',
        'keterangan'
    ];

    public function distribusi()
    {
        return $this->belongsTo(Distribusi::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(ReturDetail::class);
    }
}
