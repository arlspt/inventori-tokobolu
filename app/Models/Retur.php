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
    }
}
