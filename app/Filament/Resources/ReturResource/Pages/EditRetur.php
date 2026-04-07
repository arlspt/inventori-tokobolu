<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\ReturDetail;
use App\Models\Distribusi;
use Carbon\Carbon;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $retur = $this->record;
        $distribusi = $retur->distribusi;

        // hanya isi field tambahan saja
        $data['distribusi_info'] =
            $distribusi->reseller
            ? $distribusi->reseller->nama_reseller
            : $distribusi->tujuan_lain;

        $data['tanggal_distribusi'] =
            \Carbon\Carbon::parse($distribusi->tanggal)
            ->translatedFormat('d F Y');

        return $data;
    }
}
