<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePengadaan extends CreateRecord
{
    protected static string $resource = PengadaanResource::class;
    protected static ?string $title = 'Tambah Pengadaan'; //ubah judul halaman create menjadi "Tambah Pengadaan"
    protected static bool $canCreateAnother = false; //menghilangkan opsi tombol "Create & Create Another" pada form create

    //Mengganti label tombol simpan dan batal pada form create
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
    public function getBreadcrumb(): string
    {
        return 'Tambah Pengadaan';
    }
}
