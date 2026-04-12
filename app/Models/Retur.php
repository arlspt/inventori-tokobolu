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
    private function adjustDistribusi($retur, $mode = 'reduce')
    {
        foreach ($retur->detail as $item) {

            $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
                ->where('produk_id', $item->produk_id)
                ->first();

            if ($distribusiDetail) {
                if ($mode === 'reduce') {
                    $distribusiDetail->jumlah -= $item->jumlah;
                } else {
                    $distribusiDetail->jumlah += $item->jumlah;
                }

                $distribusiDetail->save();
            }
        }
    }
    protected static function booted()
    {
        static::creating(function ($retur) {

            $exists = self::where('distribusi_id', $retur->distribusi_id)
                ->whereNull('deleted_at')
                ->exists();

            if ($exists) {
                throw new \Exception('Retur untuk distribusi ini sudah ada.');
            }
        });
    }
}
