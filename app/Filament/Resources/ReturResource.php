<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturResource\Pages;
// use App\Filament\Resources\ReturResource\RelationManagers;
use App\Models\Retur;
use Filament\Forms;
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
                        // Forms\Components\Toggle::make('show_preview')
                        //     ->label('Tampilkan Produk Distribusi')
                        //     ->reactive(),
                        // Repeater::make('detail_preview')
                        //     ->visible(fn($get) => $get('show_preview'))
                        //     ->label('Produk Distribusi')
                        //     ->dehydrated(false)
                        //     ->default([])
                        //     ->collapsed()
                        //     ->schema([
                        //         TextInput::make('nama_produk')->disabled(),
                        //         TextInput::make('jumlah')->disabled(),
                        //     ])
                    ])
                    ->columns(2),

                // 🔹 DATA RETUR
                Section::make('Data Retur')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Retur')
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
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->schema([

                                // 🔹 KOLOM KIRI
                                Grid::make(1)
                                    ->schema([

                                        Select::make('produk_id')
                                            ->options(\App\Models\Produk::pluck('nama_produk', 'id'))
                                            ->disabled()
                                            ->label('Produk'),

                                        TextInput::make('jumlah')
                                            ->label('Jumlah Retur')
                                            ->numeric()
                                            ->required()
                                            ->live(onBlur: false)
                                            ->helperText(
                                                fn($get) =>
                                                "Maksimal retur: " . ($get('max_jumlah') ?? 0)
                                            )
                                            ->afterStateUpdated(function ($state, $get, $set) {

                                                $max = $get('max_jumlah') ?? 0;

                                                if ($state > $max) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title("Maksimal retur: $max")
                                                        ->danger()
                                                        ->send();

                                                    $set('jumlah', $max);
                                                }
                                            }),

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
                                            ->live(),

                                        Textarea::make('alasan_lain')
                                            ->label('Alasan Lainnya')
                                            ->visible(fn($get) => $get('alasan') === 'lainnya'),

                                    ])
                                    ->columnSpan(1), // 🔥 WAJIB

                                Hidden::make('max_jumlah'),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with(['distribusi.reseller', 'user']))
            ->columns([

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y'),

                TextColumn::make('distribusi_tujuan')
                    ->label('Distribusi')
                    ->getStateUsing(
                        fn($record) =>
                        $record->distribusi->reseller
                            ? $record->distribusi->reseller->nama_reseller
                            : $record->distribusi->tujuan_lain
                    ),

                TextColumn::make('detail_count')
                    ->counts('detail')
                    ->label('Jumlah Item'),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh'),
            ])
            ->headerActions([])

            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),

                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
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
