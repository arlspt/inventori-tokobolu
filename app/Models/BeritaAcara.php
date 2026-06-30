<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BeritaAcara extends Model
{
    protected $table = 'berita_acara';

    protected $fillable = [
        'user_id',
        'modul',
        'topik',
        'kategori',
        'nama_item',
        'jumlah',
        'satuan',
        'keterangan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
