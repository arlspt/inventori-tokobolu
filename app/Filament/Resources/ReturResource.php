<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturResource\Pages;
// use App\Filament\Resources\ReturResource\RelationManagers;
use App\Models\Retur;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Grid;
use Carbon\Carbon;

class ReturResource extends Resource
{
    protected static ?string $model = Retur::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Retur';
    protected static ?string $pluralModelLabel = 'Retur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // 🔹 DATA DISTRIBUSI (HARUS DI ATAS)
                Section::make('Data Distribusi')
                    ->schema([
                        TextInput::make('distribusi_info')
                            ->label('Tujuan Distribusi')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('tanggal_distribusi')
                            ->label('Tanggal Distribusi')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(2),

                // 🔹 DATA RETUR
                Section::make('Data Retur')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Retur')
                            ->displayFormat('d F Y')
                            ->format('Y-m-d')
                            ->default(now())
                            ->required(),

                        Hidden::make('distribusi_id'),

                        Hidden::make('user_id'),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ]),

                // DETAIL RETUR
                Section::make('Detail Retur')
                    ->schema([
                        Repeater::make('detail')
                            ->label('Daftar Produk Retur')
                            ->relationship()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->schema([

                                // 🔹 KOLOM KIRI
                                Grid::make(1)
                                    ->schema([

                                        Select::make('produk_id')
                                            // ->options(\App\Models\Produk::pluck('nama_produk', 'id'))
                                            ->relationship('produk', 'nama_produk')
                                            ->disabled()
                                            ->dehydrated()
                                            ->native(false)
                                            ->label('Produk'),

                                        TextInput::make('jumlah')
                                            ->label('Jumlah Retur')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: false)

                                            ->helperText(function ($get) {

                                                $produkId = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id'); // 🔥 ini kunci

                                                if (!$produkId || !$distribusiId) return null;

                                                $distribusi = \App\Models\Distribusi::with('detail')
                                                    ->find($distribusiId);

                                                if (!$distribusi) return null;

                                                $jumlahDistribusi = $distribusi->detail
                                                    ->where('produk_id', $produkId)
                                                    ->first()?->jumlah ?? 0;

                                                $sudahRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId);
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                $max = $jumlahDistribusi - $sudahRetur;

                                                return "Maksimal retur: $max";
                                            })

                                            ->rule(function ($get) {
                                                return function ($attribute, $value, $fail) use ($get) {

                                                    $produkId = $get('produk_id');
                                                    $distribusiId = $get('../../distribusi_id');

                                                    if (!$produkId || !$distribusiId) return;

                                                    $distribusi = \App\Models\Distribusi::with('detail')
                                                        ->find($distribusiId);

                                                    if (!$distribusi) return;

                                                    $jumlahDistribusi = $distribusi->detail
                                                        ->where('produk_id', $produkId)
                                                        ->first()?->jumlah ?? 0;

                                                    $sudahRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                        $q->where('distribusi_id', $distribusiId);
                                                    })
                                                        ->where('produk_id', $produkId)
                                                        ->sum('jumlah');

                                                    $max = $jumlahDistribusi - $sudahRetur;

                                                    if ($value > $max) {
                                                        $fail("Jumlah melebihi maksimal ($max)");
                                                    }
                                                };
                                            })
                                            ->afterStateUpdated(function ($state, $get, $set) {

                                                $produkId = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id');

                                                if (!$produkId || !$distribusiId) return;

                                                $distribusi = \App\Models\Distribusi::with('detail')
                                                    ->find($distribusiId);

                                                if (!$distribusi) return;

                                                $jumlahDistribusi = $distribusi->detail
                                                    ->where('produk_id', $produkId)
                                                    ->first()?->jumlah ?? 0;

                                                $sudahRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId);
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                $max = $jumlahDistribusi - $sudahRetur;

                                                if ($state > $max) {
                                                    $set('jumlah', $max);
                                                }
                                            })
                                    ])
                                    ->columnSpan(1), // 🔥 WAJIB

                                // 🔹 KOLOM KANAN
                                Grid::make(1)
                                    ->schema([

                                        Select::make('alasan')
                                            ->label('Alasan')
                                            ->options([
                                                'rusak' => 'Barang Rusak',
                                                'expired' => 'Expired',
                                                'salah_kirim' => 'Salah Kirim',
                                                'retur_pelanggan' => 'Retur Pelanggan',
                                                'lainnya' => 'Lainnya',
                                            ])
                                            ->placeholder('Pilih alasan')
                                            ->native(false)
                                            ->required()
                                            ->reactive(),

                                        Textarea::make('alasan_lain')
                                            ->label('Alasan Lainnya')
                                            ->placeholder('Masukkan alasan lainnya')
                                            ->visible(fn($get) => $get('alasan') === 'lainnya')
                                            ->requiredIf('alasan', 'lainnya'),

                                    ])
                                    ->columnSpan(1), // WAJIB

                                Hidden::make('max_jumlah'),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['detail.produk', 'distribusi.reseller', 'user']))
            ->modifyQueryUsing(fn($query) => $query->withoutTrashed())
            ->columns([

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                    ),

                TextColumn::make('distribusi_tujuan')
                    ->label('Distribusi')
                    ->getStateUsing(
                        fn($record) =>
                        $record->distribusi->reseller
                            ? $record->distribusi->reseller->nama_reseller
                            : $record->distribusi->tujuan_lain
                    ),

                TextColumn::make('total_retur')
                    ->label('Total Retur')
                    ->getStateUsing(
                        fn($record) =>
                        $record->detail->sum('jumlah')
                    ),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh'),
            ])
            ->headerActions([])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail')
                    ->infolist([

                        \Filament\Infolists\Components\Section::make('Data Distribusi')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('distribusi_id')
                                    ->label('Distribusi')
                                    ->formatStateUsing(
                                        fn($record) =>
                                        $record->distribusi->reseller
                                            ? $record->distribusi->reseller->nama_reseller
                                            : $record->distribusi->tujuan_lain
                                    ),

                                \Filament\Infolists\Components\TextEntry::make('distribusi.tanggal')
                                    ->label('Tanggal Distribusi')
                                    ->date('d F Y'),
                            ])->columns(2),

                        \Filament\Infolists\Components\Section::make('Data Retur')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('tanggal')
                                    ->label('Tanggal Retur')
                                    ->date('d F Y'),

                                \Filament\Infolists\Components\TextEntry::make('keterangan')
                                    ->label('Keterangan'),
                            ])->columns(2),

                        \Filament\Infolists\Components\Section::make('Detail Retur')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('detail')->label('Daftar Produk Retur')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('produk.nama_produk'),
                                        \Filament\Infolists\Components\TextEntry::make('jumlah')->label('Jumlah Retur'),
                                        \Filament\Infolists\Components\TextEntry::make('alasan')
                                            ->formatStateUsing(fn($state) => match ($state) {
                                                'rusak' => 'Barang Rusak',
                                                'expired' => 'Expired',
                                                'salah_kirim' => 'Salah Kirim',
                                                'retur_pelanggan' => 'Retur Pelanggan',
                                                default => $state,
                                            }),
                                    ])->columns(3)
                            ]),
                    ]),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),

                Tables\Actions\Action::make('batal')
                    ->label('Batal Retur')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn($record) => $record->delete())
                // ->filters([
                //     Tables\Filters\TrashedFilter::make(),
                // ])
                ,
            ]);
    }
    // Nonaktifkan tombol Create
    // public static function canCreate(): bool
    // {
    //     return false;
    // }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturs::route('/'),
            'create' => Pages\CreateRetur::route('/create'),
            'edit' => Pages\EditRetur::route('/{record}/edit'),
        ];
    }
}
