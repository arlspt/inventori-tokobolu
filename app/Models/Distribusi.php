<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Distribusi extends Model
{
    protected $table = 'distribusi';
    protected $fillable = [
        'tanggal',
        'reseller_id',
        'tujuan_lain',
        'user_id',
        'nomor_invoice',
        'total',
        'keterangan',
        'status',
        'status_pembayaran',
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
        // 🔥 AUTO GENERATE NOMOR INVOICE SAAT CREATE (FINAL SAVE)
        static::creating(function ($distribusi) {
            if (!$distribusi->nomor_invoice) {
                $distribusi->nomor_invoice = self::generateInvoice();
            }
        });

        // 🔥 HITUNG TOTAL SETELAH DETAIL TERSIMPAN
        static::saved(function ($distribusi) {
            $total = $distribusi->detail()->sum('subtotal');

            // hindari loop tak perlu
            if ($distribusi->total != $total) {
                $distribusi->total = $total;
                $distribusi->saveQuietly();
            }
        });

        static::deleting(function ($distribusi) {

            DB::transaction(function () use ($distribusi) {

                $distribusi->load('detail');

                foreach ($distribusi->detail as $detail) {

                    $produk = \App\Models\Produk::find($detail->produk_id);

                    if ($produk) {
                        $produk->increment('stok', $detail->jumlah);
                    }
                }
            });
        });
    }
    public function batalkan()
    {
        // 🔥 cegah double cancel
        if ($this->status === 'dibatalkan') {
            return;
        }
        DB::transaction(function () {
            $this->load('detail');
            foreach ($this->detail as $detail) {
                $produk = \App\Models\Produk::find($detail->produk_id);
                // rollback stok produk
                $produk->increment('stok', $detail->jumlah);
            }
            // update status
            $this->update([
                'status' => 'dibatalkan'
            ]);
        });
    }
    public static function generateInvoice()
    {
        $tahun = now()->year;
        $last = self::whereYear('created_at', $tahun)
            ->orderBy('id', 'desc')
            ->first();
        $next = $last
            ? ((int) substr($last->nomor_invoice, -4)) + 1
            : 1;
        return 'INV-' . $tahun . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
