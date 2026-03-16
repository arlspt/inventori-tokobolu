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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Produksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')
                    ->required(),

                Repeater::make('produksiDetail')
                    ->relationship()
                    ->schema([

                        Select::make('produk_id')
                            ->relationship('produk', 'nama_produk')
                            ->searchable()
                            ->required(),

                        TextInput::make('jumlah_produksi')
                            ->numeric()
                            ->required()

                    ])
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
            'index' => Pages\ListProduksis::route('/'),
            'create' => Pages\CreateProduksi::route('/create'),
            'edit' => Pages\EditProduksi::route('/{record}/edit'),
        ];
    }
}
