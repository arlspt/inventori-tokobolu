<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\UserModulePermission;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $roles = $data['roles'] ?? [];

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // admin biasa tidak boleh membuat admin
        if (
            $user
            && !$user->isAdminKey()
            && in_array('admin', $roles)
        ) {
            throw ValidationException::withMessages([
                'roles' => 'Hanya admin utama yang dapat membuat admin.',
            ]);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (
            $user
            && $user->hasRole('admin')
            && !$user->isAdminKey()
        ) {
            $data['roles'] = ['karyawan'];
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        /** @var \App\Models\User|null $loginUser */
        $loginUser = Auth::user();

        // admin biasa → paksa assign role karyawan
        if (
            $loginUser
            && $loginUser->hasRole('admin')
            && !$loginUser->isAdminKey()
            && !$user->roles()->exists()
        ) {
            $user->assignRole('karyawan');
        }

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
