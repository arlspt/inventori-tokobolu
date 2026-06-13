<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserModulePermission;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    protected static ?string $title = 'Ubah User';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Simpan Perubahan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Kembali');
    }

    public function getBreadcrumb(): string
    {
        return 'Ubah User';
    }

    protected function afterSave(): void
    {
        $this->simpanModulAkses();
    }

    private function simpanModulAkses(): void
    {
        $user = $this->record;

        // ✅ kalau diubah jadi admin → hapus semua permission modul
        if ($user->hasRole('admin')) {
            $user->modulePermissions()->delete();
            return;
        }

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
