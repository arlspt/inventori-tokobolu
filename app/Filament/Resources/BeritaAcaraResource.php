<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeritaAcaraResource\Pages;
use App\Models\BeritaAcara;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Carbon\Carbon;

class BeritaAcaraResource extends Resource
{
    protected static ?string $model = BeritaAcara::class;
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Berita Acara';
    protected static ?string $pluralModelLabel = 'Berita Acara';

    // ✅ hanya admin yang bisa akses
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        return $user?->hasRole('admin') ?? false;
    }

    // ✅ tidak bisa create dari halaman ini (hanya dari modul terkait)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(['user'])
                    ->orderBy('created_at', 'desc')
            )
            ->searchPlaceholder('Cari Item  ...')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('d M Y, H:i')
                    ),

                TextColumn::make('modul')
                    ->label('Modul')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'bahan_baku' => 'Bahan Baku',
                        'produksi'   => 'Produksi',
                        default      => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'bahan_baku' => 'info',
                        'produksi'   => 'success',
                        default      => 'gray',
                    }),

                TextColumn::make('kategori')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'hilang'     => 'Hilang',
                        'rusak'      => 'Rusak',
                        'cacat'      => 'Cacat',
                        'tumpah'     => 'Tumpah',
                        'kadaluarsa' => 'Kadaluarsa',
                        'lainnya'    => 'Lainnya',
                        default      => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'hilang', 'rusak', 'cacat' => 'danger',
                        'tumpah', 'kadaluarsa'     => 'warning',
                        default                    => 'gray',
                    }),

                TextColumn::make('nama_item')
                    ->label('Item')
                    ->searchable(),

                TextColumn::make('jumlah')
                    ->label('Jumlah')
                    ->getStateUsing(
                        fn($record) =>
                        // ✅ hapus .00 jika bilangan bulat
                        rtrim(rtrim(number_format($record->jumlah, 2, '.', ''), '0'), '.')
                            . ' ' . $record->satuan
                    ),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('-')
                    ->limit(50),

                TextColumn::make('user.name')
                    ->label('Dicatat Oleh'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('modul')
                    ->label('Modul')
                    ->placeholder('Semua Modul')
                    ->options([
                        'bahan_baku' => 'Bahan Baku',
                        'produksi'   => 'Produksi',
                    ]),

                Tables\Filters\SelectFilter::make('kategori')
                    ->label('Kategori')
                    ->placeholder('Semua Kategori')
                    ->options([
                        'hilang'     => 'Hilang',
                        'rusak'      => 'Rusak',
                        'cacat'      => 'Cacat',
                        'tumpah'     => 'Tumpah',
                        'kadaluarsa' => 'Kadaluarsa',
                        'lainnya'    => 'Lainnya',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->color('info')
                    ->infolist([
                        \Filament\Infolists\Components\Section::make('Informasi Berita Acara')
                            ->columns(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('created_at')
                                    ->label('Tanggal')
                                    ->formatStateUsing(
                                        fn($state) =>
                                        \Carbon\Carbon::parse($state)
                                            ->locale('id')
                                            ->translatedFormat('d M Y, H:i')
                                    ),

                                \Filament\Infolists\Components\TextEntry::make('modul')
                                    ->label('Modul')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'bahan_baku' => 'Bahan Baku',
                                        'produksi'   => 'Produksi',
                                        default      => $state,
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'bahan_baku', 'produksi' => 'success',
                                        default                  => 'gray',
                                    }),

                                \Filament\Infolists\Components\TextEntry::make('kategori')
                                    ->label('Kategori')
                                    ->badge()
                                    ->formatStateUsing(fn($state) => match ($state) {
                                        'hilang'     => 'Hilang',
                                        'rusak'      => 'Rusak',
                                        'cacat'      => 'Cacat',
                                        'tumpah'     => 'Tumpah',
                                        'kadaluarsa' => 'Kadaluarsa',
                                        'lainnya'    => 'Lainnya',
                                        default      => $state,
                                    })
                                    ->color(fn($state) => match ($state) {
                                        'hilang', 'rusak', 'cacat' => 'danger',
                                        'tumpah', 'kadaluarsa'     => 'warning',
                                        default                    => 'gray',
                                    }),

                                \Filament\Infolists\Components\TextEntry::make('nama_item')
                                    ->label('Item Terdampak'),

                                \Filament\Infolists\Components\TextEntry::make('jumlah')
                                    ->label('Jumlah')
                                    ->getStateUsing(
                                        fn($record) =>
                                        rtrim(rtrim(number_format($record->jumlah, 2, '.', ''), '0'), '.')
                                            . ' ' . $record->satuan
                                    ),
                                \Filament\Infolists\Components\TextEntry::make('user.name')
                                    ->label('Dicatat Oleh'),

                                \Filament\Infolists\Components\TextEntry::make('keterangan')
                                    ->label('Keterangan')
                                    ->columnSpanFull()
                                    ->placeholder('-'),
                            ]),
                    ]),
            ])
            ->recordUrl(null)
            ->recordAction('view') // ✅ klik baris → buka view detail            ->actionsColumnLabel('Aksi')
            ->bulkActions([]); // ✅ tidak ada bulk action
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeritaAcaras::route('/'),
        ];
    }
}
