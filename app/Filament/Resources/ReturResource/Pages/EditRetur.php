<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

use Carbon\Carbon;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;
    protected static ?string $title = 'Ubah Retur'; //ubah judul halaman edit menjadi "Ubah Retur"

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
            Carbon::parse($distribusi->tanggal)
            ->translatedFormat('d F Y');

        return $data;
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
        return 'Ubah Retur';
    }
}
