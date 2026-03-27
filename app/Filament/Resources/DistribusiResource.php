<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistribusiResource\Pages;
// use App\Filament\Resources\DistribusiResource\RelationManagers;
use App\Models\Distribusi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class DistribusiResource extends Resource
{
    protected static ?string $model = Distribusi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Distribusi';
    protected static ?string $pluralModelLabel = 'Distribusi';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION DATA UTAMA
                Section::make('Data Distribusi')
                    ->columns(2)
                    ->schema([

                        Grid::make(1)
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->label('Tanggal')
                                    ->default(now())
                                    ->required(),

                                Hidden::make('user_id')
                                    ->default(fn() => Auth::id()),

                                Select::make('reseller_id')
                                    ->label('Reseller')
                                    ->relationship('reseller', 'nama_reseller')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('nama_reseller')
                                            ->label('Nama Reseller')
                                            ->required(),

                                        TextInput::make('alamat')
                                            ->label('Alamat')
                                            ->required(),

                                        TextInput::make('no_telp')
                                            ->label('No. Telepon')
                                            ->tel()
                                            ->required(),
                                    ])
                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->label('Tambah Reseller')
                                            ->modalHeading('Tambah Reseller')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    })
                                    ->requiredWithout('tujuan_lain')
                                    ->dehydrated(),
                                TextInput::make('tujuan_lain')
                                    ->label('Tujuan Lain')
                                    ->placeholder('Isi jika bukan reseller')
                                    ->requiredWithout('reseller_id')
                                    ->dehydrated(),
                            ])
                            ->columnSpan(1),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->rows(8)
                            ->columnSpan(1),
                    ]),


                // SECTION DETAIL (INI REPEATER)
                Section::make('Detail Produk')
                    ->schema([

                        Repeater::make('detail') // nama relasi
                            ->relationship()
                            ->columns(2)
                            ->schema([

                                Select::make('produk_id')
                                    ->label('Produk')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $set) {
                                        // set default jumlah = 1
                                        $set('jumlah', 1);

                                        // ambil harga dari produk
                                        $produk = \App\Models\Produk::find($state);
                                        if ($produk) {
                                            $set('harga', $produk->harga);
                                            $set('subtotal', $produk->harga * 1);
                                        }
                                    }),
                                TextInput::make('harga')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated(), // tetap disimpan ke DB
                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $harga = $get('harga') ?? 0;
                                        $set('subtotal', $harga * $state);
                                    }),
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated()
                            ])
                            ->addAction(
                                fn($action) =>
                                $action
                                    ->label('Tambah Produk')
                                    ->icon('heroicon-m-plus')
                            )
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->getStateUsing(
                        fn($record) =>
                        $record->reseller
                            ? $record->reseller->nama_reseller
                            : $record->tujuan_lain
                    ),

                TextColumn::make('detail_count')
                    ->counts('detail')
                    ->label('Jumlah Item'),

                TextColumn::make('total')
                    ->getStateUsing(
                        fn($record) =>
                        $record->detail->sum('subtotal')
                    )
                    ->label('Total')
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format($state, 0, ',', '.') // format mata uang Indonesia
                    ),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make(),
            ])
            ->actionsColumnLabel('Aksi')
            ->actionsPosition(\Filament\Tables\Enums\ActionsPosition::AfterColumns)

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
            'index' => Pages\ListDistribusis::route('/'),
            'create' => Pages\CreateDistribusi::route('/create'),
            'edit' => Pages\EditDistribusi::route('/{record}/edit'),
        ];
    }
}
