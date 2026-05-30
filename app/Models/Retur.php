<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

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
    // private function adjustDistribusi($retur, $mode = 'reduce')
    // {
    //     foreach ($retur->detail as $item) {
    //         $distribusiDetail = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
    //             ->where('produk_id', $item->produk_id)
    //             ->first();
    //         if ($distribusiDetail) {
    //             if ($mode === 'reduce') {
    //                 $distribusiDetail->jumlah -= $item->jumlah;
    //             } else {
    //                 $distribusiDetail->jumlah += $item->jumlah;
    //             }
    //             $distribusiDetail->save();
    //         }
    //     }
    // }
    protected static function booted()
    {
        static::creating(function ($retur) {

            DB::transaction(function () use ($retur) {

                $exists = self::where('distribusi_id', $retur->distribusi_id)
                    ->whereNull('deleted_at')
                    ->lockForUpdate()
                    ->exists();
                if ($exists) {
                    throw new \Exception('Retur untuk distribusi ini sudah ada.');
                }
                $year = now()->year;
                $last = self::withTrashed()
                    ->whereYear('created_at', $year)
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();
                $number = $last
                    ? (int) substr($last->nomor_retur, -4) + 1
                    : 1;
                $retur->nomor_retur =
                    'RET-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            });
        });
        // static::deleting(function ($retur) {
        //     $retur->load('detail');
        //     foreach ($retur->detail as $detail) {
        //         if ((int) $detail->jumlah <= 0) continue;
        //         // kembalikan jumlah ke distribusi_detail
        //         $dist = \App\Models\DistribusiDetail::where('distribusi_id', $retur->distribusi_id)
        //             ->where('produk_id', $detail->produk_id)
        //             ->first();
        //         if ($dist) {
        //             $dist->increment('jumlah', $detail->jumlah);
        //         }
        //         // ✅ kembalikan stok hanya kalau kondisi baik
        //         if ($detail->kondisi === 'baik') {
        //             $produk = \App\Models\Produk::find($detail->produk_id);
        //             if ($produk) {
        //                 $produk->decrement('stok', $detail->jumlah);
        //             }
        //         }
        //     }
        // });
    }
}
