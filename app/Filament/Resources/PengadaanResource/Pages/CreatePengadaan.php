<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePengadaan extends CreateRecord
{
    protected static string $resource = PengadaanResource::class;
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
}
