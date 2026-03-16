<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengadaanResource\Pages;
// use App\Filament\Resources\PengadaanResource\RelationManagers;
use App\Models\Pengadaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class PengadaanResource extends Resource
{
    protected static ?string $model = Pengadaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Pengadaan Bahan Baku';



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

                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->required(),

                        TextInput::make('kode_pengadaan')
                            ->label('Kode Pengadaan')
                            ->disabled()
                            ->default('AUTO'),
                    ]),
                Section::make('Detail Bahan')
                    ->schema([

                        Repeater::make('pengadaanDetail')
                            ->relationship()
                            ->columns(4)
                            ->schema([

                                Select::make('bahan_baku_id')
                                    ->label('Bahan')
                                    ->relationship('bahanBaku', 'nama_bahan')
                                    ->searchable()
                                    ->required(),

                                TextInput::make('qty')
                                    ->label('Qty')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('harga')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->disabled(),

                            ])

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
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
            'index' => Pages\ListPengadaans::route('/'),
            'create' => Pages\CreatePengadaan::route('/create'),
            'edit' => Pages\EditPengadaan::route('/{record}/edit'),
        ];
    }
}
