<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPengadaans extends ListRecords
{
    protected static string $resource = PengadaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pengadaan')
                ->modalHeading('Tambah Pengadaan Bahan Baku')
                ->modalWidth('7xl'),
        ];
    }
}
