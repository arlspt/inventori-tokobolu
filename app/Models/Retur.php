<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Retur extends Model
{
    use SoftDeletes;
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
