<?php

namespace App\Filament\Resources\ReturResource\Pages;

use App\Filament\Resources\ReturResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EditRetur extends EditRecord
{
    protected static string $resource = ReturResource::class;
    protected static ?string $title = 'Ubah Retur';

    // ✅ simpan snapshot nilai lama sebelum disimpan
    // protected array $snapshotSebelumSimpan = [];

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

        $data['distribusi_info'] =
            $distribusi->reseller
            ? $distribusi->reseller->nama_reseller
            : $distribusi->tujuan_lain;

        $data['tanggal_distribusi'] =
            Carbon::parse($distribusi->tanggal)
            ->translatedFormat('d F Y');

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // pastikan user_id tidak pernah null saat edit
        if (empty($data['user_id'])) {
            $data['user_id'] = $this->record->user_id;
        }

        return $data;
    }

    protected function beforeSave(): void
    {
        // ✅ simpan nilai lama dari DB sebelum Filament update
        // $retur = $this->record;
        // $retur->load('detail');

        // $this->snapshotSebelumSimpan = $retur->detail->mapWithKeys(function ($detail) {
        //     return [$detail->id => [
        //         'jumlah'  => $detail->jumlah,
        //         'kondisi' => $detail->kondisi,
        //         'produk_id' => $detail->produk_id,
        //     ]];
        // })->toArray();
    }

    protected function afterSave(): void
    {
        // $retur = $this->record;
        // $retur->refresh();
        // $retur->load('detail');

        // DB::transaction(function () use ($retur) {
        //     foreach ($retur->detail as $detail) {

        //         $lama = $this->snapshotSebelumSimpan[$detail->id] ?? null;

        //         $jumlahLama  = $lama ? (int) $lama['jumlah'] : 0;
        //         $kondisiLama = $lama ? $lama['kondisi'] : null;
        //         $jumlahBaru  = (int) $detail->jumlah;
        //         $kondisiBaru = $detail->kondisi;

        //         // ✅ hitung selisih jumlah saja
        //         $selisih = $jumlahBaru - $jumlahLama;

        //         // update stok berdasarkan perubahan kondisi dan jumlah
        //         if ($kondisiLama === 'baik' && $kondisiBaru === 'baik') {
        //             // kondisi sama-sama baik, update selisihnya
        //             if ($selisih !== 0) {
        //                 $produk = \App\Models\Produk::find($detail->produk_id);
        //                 if ($produk) {
        //                     if ($selisih > 0) {
        //                         $produk->increment('stok', $selisih);
        //                     } else {
        //                         $produk->decrement('stok', abs($selisih));
        //                     }
        //                 }
        //             }
        //         } elseif ($kondisiLama !== 'baik' && $kondisiBaru === 'baik') {
        //             // kondisi berubah jadi baik → tambah stok sejumlah baru
        //             $produk = \App\Models\Produk::find($detail->produk_id);
        //             if ($produk) {
        //                 $produk->increment('stok', $jumlahBaru);
        //             }
        //         } elseif ($kondisiLama === 'baik' && $kondisiBaru !== 'baik') {
        //             // kondisi berubah jadi rusak → kurangi stok sejumlah lama
        //             $produk = \App\Models\Produk::find($detail->produk_id);
        //             if ($produk) {
        //                 $produk->decrement('stok', $jumlahLama);
        //             }
        //         }
        //         // kondisi sama-sama rusak → tidak ada perubahan stok
        //     }
        // });
    }

    protected function afterFill(): void
    {
        $retur = $this->record;
        $distribusi = $retur->distribusi;

        if (!$distribusi) return;

        // $this->form->fill([
        //     'distribusi_id'      => $distribusi->id,
        //     'tanggal'            => $retur->tanggal,
        //     'distribusi_info'    => $distribusi->reseller
        //         ? $distribusi->reseller->nama_reseller
        //         : $distribusi->tujuan_lain,
        //     'tanggal_distribusi' => Carbon::parse($distribusi->tanggal)->translatedFormat('d F Y'),
        //     'nomor_invoice'      => $distribusi->nomor_invoice ?? '-',
        // ]);
        $this->form->getComponent('data.distribusi_info')
            ?->state($distribusi->reseller
                ? $distribusi->reseller->nama_reseller
                : $distribusi->tujuan_lain);

        $this->form->getComponent('data.tanggal_distribusi')
            ?->state(Carbon::parse($distribusi->tanggal)->translatedFormat('d F Y'));

        $this->form->getComponent('data.nomor_invoice')
            ?->state($distribusi->nomor_invoice ?? '-');
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
        return 'Ubah Retur';
    }
}
