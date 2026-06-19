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

    protected function bolehDicatat($model): bool
    {
        return in_array(
            class_basename($model),
            [

                // MASTER
                'User',

                // TRANSAKSI UTAMA
                'Pengadaan',
                'Produksi',
                'Distribusi',
                'Retur',

                // opsional
                'Supplier',
                'Reseller',
            ]
        );
    }

    protected function buatLog(
        $model,
        string $aktivitas
    ): void {

        if (!Auth::check()) {
            return;
        }

        // CEGAH LOG DUPLIKAT
        if (!$this->bolehDicatat($model)) {
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

            'Pengadaan'
            => 'pengadaan bahan baku',

            'Produksi'
            => 'produksi',

            'Distribusi'
            => 'distribusi',

            'Retur'
            => 'retur',

            'Supplier'
            => 'supplier',

            'Reseller'
            => 'reseller',

            'User'
            => 'manajemen user',

            default =>
            strtolower(class_basename($model)),
        };
    }
}
