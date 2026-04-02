<?php

namespace App\Filament\Resources\ProduksiResource\Pages;

use App\Filament\Resources\ProduksiResource;
use Filament\Actions\Action;
// use Filament\Actions\CreateAction;
use Filament\Resources\Pages\CreateRecord;

class CreateProduksi extends CreateRecord
{
    protected static string $resource = ProduksiResource::class;
    protected static ?string $title = 'Tambah Produksi'; //ubah judul halaman create menjadi "Tambah Produksi"
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
    // mengubah breadcrumb pada halaman create menjadi "Tambah Produksi"
    public function getBreadcrumb(): string
    {
        return 'Tambah Produksi';
    }
    // protected function getFormActions(): array
    // {
    //     return [
    //         CreateAction::make()
    //             ->label('Simpan'),
    //     ];
    // }
}
