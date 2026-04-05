<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Distribusi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class CreateRetur extends CreateRecord
{

    protected static string $resource = ReturResource::class;
    public function mount(): void
    {
        parent::mount();

        $distribusiId = request()->get('distribusi_id');

        if (!$distribusiId) {
            return;
        }

        $distribusi = Distribusi::with(['detail.produk', 'reseller'])
            ->find($distribusiId);

        if (!$distribusi) {
            return;
        }

        // 🔹 isi form langsung
        $this->form->fill([

            'distribusi_id' => $distribusi->id,

            'distribusi_info' =>
            $distribusi->reseller
                ? $distribusi->reseller->nama_reseller
                : $distribusi->tujuan_lain,

            'tanggal_distribusi' =>
            Carbon::parse($distribusi->tanggal)
                ->locale('id')
                ->translatedFormat('d F Y'),

            // 🔹 preview
            'detail_preview' => $distribusi->detail->map(fn($item) => [
                'nama_produk' => $item->produk->nama_produk,
                'jumlah' => $item->jumlah,
            ])->toArray(),

            // 🔹 detail retur
            'detail' => $distribusi->detail->map(function ($item) {
                return [
                    'produk_id' => $item->produk_id,
                    'jumlah' => 0,
                    'max_jumlah' => $item->jumlah,
                    'alasan' => null,
                ];
            })->toArray(),

        ]);
    }
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
        $data['user_id'] = Auth::id();

        return $data;
    }
}
