<?php

namespace App\Filament\Resources\BeritaAcaraResource\Pages;

use App\Filament\Resources\BeritaAcaraResource;
use Filament\Resources\Pages\ListRecords;

class ListBeritaAcaras extends ListRecords
{
    protected static string $resource = BeritaAcaraResource::class;

    protected function getHeaderActions(): array
    {
        return []; // ✅ tidak ada tombol tambah
    }
}