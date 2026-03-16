<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
// use App\Filament\Resources\ProdukResource\RelationManagers;
use App\Models\Produk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProdukResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_produk')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('harga')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('stok')
                    ->numeric()
                    ->default(0)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_produk')->searchable(),
                Tables\Columns\TextColumn::make('harga'),
                Tables\Columns\TextColumn::make('stok'),
                Tables\Columns\TextColumn::make('created_at')->date(),
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
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
        ];
    }
}
