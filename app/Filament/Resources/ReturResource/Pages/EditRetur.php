<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
