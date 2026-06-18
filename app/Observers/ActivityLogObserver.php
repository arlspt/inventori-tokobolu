<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    public bool $afterCommit = true;

    public function created($model): void
    {
        $this->buatLog($model, 'Tambah');
    }

    public function updated($model): void
    {
        // hanya jika memang ada perubahan
        if ($model->getChanges()) {
            $this->buatLog($model, 'Ubah');
        }
    }

    public function deleted($model): void
    {
        $this->buatLog($model, 'Hapus');
    }

    protected function buatLog(
        $model,
        string $aktivitas
    ): void {

        if (!Auth::check()) {
            return;
        }

        LogAktivitas::create([

            'user_id' => Auth::id(),

            'modul' => $this->namaModul($model),

            'aktivitas' => $aktivitas,

            'deskripsi' => match ($aktivitas) {

                'Tambah'
                => 'Menambahkan data ' . $this->namaModul($model),

                'Ubah'
                => 'Mengubah data ' . $this->namaModul($model),

                'Hapus'
                => 'Menghapus data ' . $this->namaModul($model),
            },
        ]);
    }

    protected function namaModul($model): string
    {
        return match (class_basename($model)) {

            // Pengadaan
            'Pengadaan',
            'PengadaanDetail'
            => 'pengadaan bahan baku',

            // Produksi
            'Produksi',
            'ProduksiDetail'
            => 'produksi',

            // Distribusi
            'Distribusi',
            'DistribusiDetail'
            => 'distribusi',

            // Retur
            'Retur',
            'ReturDetail',
            'DetailRetur'
            => 'retur',

            // Master
            'Supplier'
            => 'supplier',

            'Reseller'
            => 'reseller',

            'User'
            => 'manajemen user',

            default =>
            strtolower(
                class_basename($model)
            ),
        };
    }
}
