<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengadaanResource\Pages;
use App\Models\Pengadaan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
// use Filament\Forms\Components\Actions\Action;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengadaanResource extends Resource
{
    protected static ?string $model = Pengadaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Pengadaan Bahan Baku';
    protected static ?string $modelLabel = 'Pengadaan';
    protected static ?string $pluralModelLabel = 'Pengadaan Bahan Baku';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Data Pengadaan')
                    ->columns(2)
                    ->schema([

                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->required(),

                        // TextInput::make('kode_pengadaan')
                        //     ->label('Kode Pengadaan')
                        //     ->disabled()
                        //     // ->dehydrated()
                        //     ->default('AUTO'),
                    ]),
                Section::make('Detail Bahan')
                    ->schema([
                        Repeater::make('pengadaanDetail')
                            ->relationship()
                            ->columns(4)
                            ->deleteAction(
                                fn($action) => $action
                                    ->label('Hapus')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                            )
                            ->schema([

                                Select::make('bahan_baku_id')
                                    ->label('Bahan Baku')
                                    ->relationship('bahanBaku', 'nama_bahan')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('nama_bahan')
                                            ->required(),

                                        TextInput::make('stok')
                                            ->prefix('Kg')
                                            ->formatStateUsing(function ($state) {
                                                return $state >= 1000
                                                    ? ($state / 1000) . ' kg'
                                                    : $state . ' 1';
                                            })
                                    ])
                                    ->required(),

                                TextInput::make('harga')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->required(),

                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('subtotal')
                                    // ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ])
                            ->addActionLabel('Tambah Pengadaan Detail')
                            ->collapsible()

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with('pengadaanDetail'))
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y');
                    }),

                TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier'),

                TextColumn::make('jumlah_item')
                    ->label('Jumlah Bahan')
                    ->getStateUsing(fn($record) => $record->pengadaanDetail->count()),

                TextColumn::make('total_harga')
                    ->label('Total')
                    ->getStateUsing(function ($record) {
                        return 'Rp. ' . number_format(
                            $record->pengadaanDetail->sum('subtotal'),
                            0,
                            ',',
                            '.'
                        );
                    }),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()->label('Hapus'),
            ])
            ->actionsColumnLabel('Aksi')
            // ->actionsAlignment('start')
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
    // public static function getNavigationLabel(): string
    // {
    //     return 'Pengadaannnn';
    // }
    // protected function getCreateFormAction(): Action
    // {
    //     return parent::getCreateFormAction()
    //         ->label('Simpan');
    // }

    // protected function getCancelFormAction(): Action
    // {
    //     return parent::getCancelFormAction()
    //         ->label('Batal');
    // }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPengadaans::route('/'),
            'create' => Pages\CreatePengadaan::route('/create'),
            'edit' => Pages\EditPengadaan::route('/{record}/edit'),
            'view' => Pages\ViewPengadaan::route('/{record}'),
        ];
    }
}
