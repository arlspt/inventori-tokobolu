<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Distribusi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\Action;

class CreateRetur extends CreateRecord
{

    protected static string $resource = ReturResource::class;
    protected static bool $canCreateAnother = false; //menghilangkan opsi tombol "Create & Create Another" pada form create


    // Ubah Judul Page Dinamis
    public function getTitle(): string
    {
        $distribusiId = request()->get('distribusi_id');

        if ($distribusiId) {
            $distribusi = Distribusi::with('reseller')->find($distribusiId);

            $tujuan = $distribusi->reseller
                ? $distribusi->reseller->nama_reseller
                : $distribusi->tujuan_lain;

            return 'Retur ' . $tujuan;
        }

        return 'Retur';
    }
    //Tambahkan Breadcrumb
    public function getBreadcrumb(): string
    {
        return 'Retur';
    }
    // Redirect balik ke Distribusi setelah simpan
    public function getBreadcrumbs(): array
    {
        return [
            route('filament.admin.resources.distribusis.index') => 'Distribusi',
            '' => 'Retur',
        ];
    }
    public function getHeaderWidgets(): array
    {
        return [];
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth::id();
        return $data;
    }
    public function mount(): void
    {
        parent::mount();

        $distribusiId = request()->get('distribusi_id');

        if (!$distribusiId) return;

        $distribusi = Distribusi::with(['detail.produk', 'reseller'])
            ->find($distribusiId);

        if (!$distribusi) return;

        // isi semua field display
        $this->form->fill([
            'distribusi_id' => $distribusi->id,

            'distribusi_info' =>
            $distribusi->reseller
                ? $distribusi->reseller->nama_reseller
                : $distribusi->tujuan_lain,

            'tanggal_distribusi' =>
            Carbon::parse($distribusi->tanggal)
                ->translatedFormat('d F Y'),
        ]);

        // isi repeater
        $this->form->getComponent('data.detail')->state(
            $distribusi->detail->map(function ($item) {
                $max = $item->jumlah_awal ?? $item->jumlah;

                return [
                    'produk_id' => $item->produk_id,
                    'jumlah' => 0,
                    'max_jumlah' => $max,
                    'alasan' => null,
                ];
            })->toArray()
        );
    }
    //Mengganti label tombol simpan dan batal pada form create
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }
}
