<?php

namespace App\Filament\Resources\LogAktivitasResource\Pages;

use Filament\Actions;

use Filament\Resources\Pages\ListRecords;

use App\Filament\Resources\LogAktivitasResource;

class ListLogAktivitas
extends ListRecords
{
    protected static string $resource =
    LogAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
