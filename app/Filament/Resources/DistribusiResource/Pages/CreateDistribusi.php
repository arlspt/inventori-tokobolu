<?php

namespace App\Filament\Resources\DistribusiResource\Pages;

use App\Filament\Resources\DistribusiResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateDistribusi extends CreateRecord
{
    protected static string $resource = DistribusiResource::class;
    protected static ?string $title = 'Tambah Distribusi'; //ubah judul halaman create menjadi "Tambah Distribusi"
    protected static bool $canCreateAnother = false; //menghilangkan opsi tombol "Create & Create Another" pada form

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
        return 'Tambah Distribusi';
    }
}
