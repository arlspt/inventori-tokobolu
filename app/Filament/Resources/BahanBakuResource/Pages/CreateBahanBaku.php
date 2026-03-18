<?php

namespace App\Filament\Resources\BahanBakuResource\Pages;

use App\Filament\Resources\BahanBakuResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBahanBaku extends CreateRecord
{
    protected static string $resource = BahanBakuResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $stok = (int) $data['stok_input'];

        if ($data['satuan'] === 'kg') {
            $data['stok'] = $stok * 1000;
        } else {
            $data['stok'] = $stok;
        }

        unset($data['stok_input']);

        return $data;
    }
}
