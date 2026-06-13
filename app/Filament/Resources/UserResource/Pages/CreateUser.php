<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserModulePermission;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $title = 'Tambah User';
    protected static bool $canCreateAnother = false;

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Batal');
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah User';
    }

    protected function afterCreate(): void
    {
        $this->simpanModulAkses();
    }

    private function simpanModulAkses(): void
    {
        $user = $this->record;

        // ✅ hanya proses kalau role karyawan
        if (!$user->hasRole('karyawan')) return;

        $modulAkses = $this->data['modul_akses'] ?? [];
        $semuaModul = ['pengadaan', 'produksi', 'distribusi', 'retur'];

        foreach ($semuaModul as $modul) {
            UserModulePermission::updateOrCreate(
                ['user_id' => $user->id, 'modul' => $modul],
                ['dapat_akses' => in_array($modul, $modulAkses)]
            );
        }
    }
}
