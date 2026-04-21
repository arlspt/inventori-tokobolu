<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditPengadaan extends EditRecord
{
    protected static string $resource = PengadaanResource::class;
    protected static ?string $title = 'Ubah Pengadaan'; //ubah judul halaman create menjadi "Tambah Pengadaan"

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    //Mengganti label tombol simpan dan batal pada form create
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Perubahan');
    }
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
    public function getBreadcrumb(): string
    {
        return 'Ubah Pengadaan';
    }
}
