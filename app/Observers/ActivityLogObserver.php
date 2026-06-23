<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;

class ActivityLogObserver
{
    public bool $afterCommit = true;

    public function created($model): void
    {
        // Detail tidak dicatat saat tambah
        if (
            !$this->bolehDicatat($model)
            || $this->modelDetail($model)
        ) {
            return;
        }

        $this->buatLog(
            $model,
            'Tambah'
        );
    }

    public function updated($model): void
    {
        if (
            !$this->bolehDicatat($model)
            || !$model->wasChanged()
        ) {
            return;
        }

        // kalau update detail → anggap update modul induk
        if (
            $this->modelDetail($model)
        ) {
            $model =
                $this->parentModel($model)
                ?? $model;
        }

        $this->buatLog(
            $model,
            'Ubah'
        );
    }

    public function deleted($model): void
    {
        // Detail tidak dicatat saat hapus
        if (
            !$this->bolehDicatat($model)
            || $this->modelDetail($model)
        ) {
            return;
        }

        $this->buatLog(
            $model,
            'Hapus'
        );
    }

    protected function modelDetail(
        $model
    ): bool {

        return in_array(
            class_basename($model),
            [
                'PengadaanDetail',
                'ProduksiDetail',
                'DistribusiDetail',
                'ReturDetail',
                'DetailRetur',
            ]
        );
    }

    protected function parentModel(
        $model
    ) {
        return match (class_basename($model)) {

            'PengadaanDetail'
            => $model->pengadaan,

            'ProduksiDetail'
            => $model->produksi,

            'DistribusiDetail'
            => $model->distribusi,

            'ReturDetail',
            'DetailRetur'
            => $model->retur,

            default => null,
        };
    }

    protected function bolehDicatat($model): bool
    {
        return in_array(
            class_basename($model),
            [
                'User',

                'Pengadaan',
                'PengadaanDetail',

                'Produksi',
                'ProduksiDetail',

                'Distribusi',
                'DistribusiDetail',

                'Retur',
                'ReturDetail',
                'DetailRetur',

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

        LogAktivitas::create([
            'user_id' => Auth::id(),

            'modul' =>
            $this->namaModul($model),

            'aktivitas' =>
            $aktivitas,

            'deskripsi' =>
            match ($aktivitas) {

                'Tambah'
                =>
                'Menambahkan data '
                    . $this->namaModul($model),

                'Ubah'
                =>
                'Mengubah data '
                    . $this->namaModul($model),

                'Hapus'
                =>
                'Menghapus data '
                    . $this->namaModul($model),
            },
        ]);
    }

    protected function namaModul(
        $model
    ): string {

        return match (class_basename($model)) {

            'Pengadaan',
            'PengadaanDetail'
            => 'pengadaan bahan baku',

            'Produksi',
            'ProduksiDetail'
            => 'produksi',

            'Distribusi',
            'DistribusiDetail'
            => 'distribusi',

            'Retur',
            'ReturDetail',
            'DetailRetur'
            => 'retur',

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