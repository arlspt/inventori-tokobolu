<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduksiResource\Pages;
// use App\Filament\Resources\ProduksiResource\RelationManagers;
use App\Models\Produksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Actions\ActionGroup;
use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $pluralModelLabel = 'Produksi';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ===== SECTION DATA =====
                Section::make('Data Produksi')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('tanggal')
                            ->label('Tanggal Produksi')
                            ->default(now())
                            ->required(),

                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                    ]),

                // ===== SECTION DETAIL =====
                Section::make('Detail Produksi')
                    ->schema([
                        Repeater::make('produksiDetail')
                            ->relationship()
                            ->label('Daftar Produksi')
                            ->addActionLabel('Tambah Produk') // Ubah label tombol "Add Item" menjadi "Tambah Produk"
                            ->columns(3)
                            ->schema([
                                // 🔥 PRODUK + TAMBAH LANGSUNG
                                Select::make('produk_id')
                                    ->label('Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        // default jumlah = 1
                                        if (!$get('jumlah_produksi')) {
                                            $set('jumlah_produksi', 1);
                                        }
                                    })

                                    ->createOptionForm([
                                        TextInput::make('nama_produk')
                                            ->label('Nama Produk')
                                            ->required(),

                                        TextInput::make('harga')
                                            ->label('Harga')
                                            ->numeric()
                                            ->prefix('Rp.')
                                            ->required(),
                                    ])

                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->label('Tambah Produk')
                                            ->modalHeading('Tambah Produk Baru')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    }),

                                // 🔥 JUMLAH
                                TextInput::make('jumlah_produksi')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->live(),

                                // 🔥 GAGAL
                                TextInput::make('gagal')
                                    ->label('Produk Gagal')
                                    ->numeric()
                                    ->default(0),

                            ])
                            ->reorderable(false)
                            ->itemLabel(fn() => 'Produk'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null) // menonaktifkan klik pada baris untuk melihat detail
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Hari, Tanggal')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y');
                    }),

                TextColumn::make('user_id')
                    ->label('Dibuat Oleh')
                    ->getStateUsing(function ($record) {
                        return $record->user ? $record->user->name : '-';
                    }),

                TextColumn::make('produksiDetail')
                    ->label('Jumlah Produk')
                    ->getStateUsing(function ($record) {

                        return $record->produksiDetail->sum(function ($item) {
                            return ($item->jumlah_produksi ?? 0) - ($item->gagal ?? 0);
                        });
                    }),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->label('Dibuat Pada'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // ViewAction::make('view')
                //     ->modalHeading('Detail Produksi')
                //     ->label('Detail')
                //     ->color('info')
                //     ->hiddenLabel() // atau ->label('')
                //     ->infolist([
                //         RepeatableEntry::make('produksiDetail')
                //             ->label(fn($record) => Carbon::parse($record->tanggal)
                //                 ->locale('id')
                //                 ->translatedFormat('l, d F Y'))
                //             ->schema([
                //                 TextEntry::make('produk.nama_produk')
                //                     ->label('Produk'),
                //                 TextEntry::make('jumlah_produksi')
                //                     ->label('Jumlah'),
                //                 TextEntry::make('gagal')
                //                     ->label('Produk Gagal'),
                //             ])
                //             ->columns(3),
                //     ]),
                ActionGroup::make([
                    ViewAction::make('view')
                        ->modalHeading('Detail Produksi')
                        ->label('Detail')
                        ->color('info')
                        ->infolist([
                            RepeatableEntry::make('produksiDetail')
                                ->label(fn($record) => Carbon::parse($record->tanggal)
                                    ->locale('id')
                                    ->translatedFormat('l, d F Y'))
                                ->schema([
                                    TextEntry::make('produk.nama_produk')
                                        ->label('Produk'),
                                    TextEntry::make('jumlah_produksi')
                                        ->label('Jumlah'),
                                    TextEntry::make('gagal')
                                        ->label('Produk Gagal'),
                                ])
                                ->columns(3),
                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()->label('Hapus'),
                ])->color('black'), //ubah warna burger menu aksi
            ])
            ->actionsColumnLabel('Aksi')
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduksis::route('/'),
            'create' => Pages\CreateProduksi::route('/create'),
            'edit' => Pages\EditProduksi::route('/{record}/edit'),
        ];
    }
}
