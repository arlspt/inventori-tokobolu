<?php

namespace App\Filament\Resources\DistribusiResource\Pages;

use App\Filament\Resources\DistribusiResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditDistribusi extends EditRecord
{
    protected static string $resource = DistribusiResource::class;
    protected static ?string $title = 'Ubah Distribusi'; //ubah judul halaman create menjadi "Tambah Distribusi"
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
        return 'Ubah Distribusi';
    }
}
