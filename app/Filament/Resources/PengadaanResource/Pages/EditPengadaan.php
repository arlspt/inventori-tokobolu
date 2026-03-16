<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengadaan extends EditRecord
{
    protected static string $resource = PengadaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
