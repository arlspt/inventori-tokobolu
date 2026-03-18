<?php

namespace App\Filament\Resources\PengadaanResource\Pages;

use App\Filament\Resources\PengadaanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\RepeatableEntry;

class ViewPengadaan extends ViewRecord
{
    protected static string $resource = PengadaanResource::class;
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                // 🔹 INFORMASI UTAMA
                Section::make('Informasi Pengadaan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->date('l, d M Y'),

                        TextEntry::make('supplier.nama_supplier')
                            ->label('Supplier'),

                        TextEntry::make('user.name')
                            ->label('Dibuat Oleh'),

                        // TextEntry::make('kode_pengadaan')
                        //     ->label('Kode Pengadaan'),
                    ]),

                // 🔹 DETAIL BAHAN
                Section::make('Detail Bahan')
                    ->schema([
                        RepeatableEntry::make('pengadaanDetail')
                            ->label('')
                            ->schema([
                                TextEntry::make('bahanBaku.nama_bahan')
                                    ->label('Bahan'),

                                TextEntry::make('jumlah')
                                    ->label('Qty'),

                                TextEntry::make('harga')
                                    ->label('Harga Satuan')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('subtotal')
                                    ->label('Subtotal')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                            ])
                            ->columns(4),
                    ]),

                // 🔹 TOTAL
                Section::make('Total')
                    ->schema([
                        TextEntry::make('total')
                            ->label('Total Keseluruhan')
                            ->getStateUsing(
                                fn($record) =>
                                'Rp. ' . number_format(
                                    $record->pengadaanDetail->sum('subtotal'),
                                    0,
                                    ',',
                                    '.'
                                )
                            ),
                    ]),
            ]);
    }
}
