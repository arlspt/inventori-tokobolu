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
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ProdukResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = ' '; // kosongin biar tidak terlihat
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_produk')
                    ->required()
                    ->maxLength(255),

                TextInput::make('harga')
                    ->numeric()
                    ->required(),

                Section::make('Resep')
                    ->schema([

                        Repeater::make('resep')
                            ->relationship()
                            ->label('Komposisi Bahan')
                            ->schema([

                                Select::make('bahan_baku_id')
                                    ->relationship('bahanBaku', 'nama_bahan')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->required()
                                    ->suffix(fn($get) => match (\App\Models\BahanBaku::find($get('bahan_baku_id'))?->satuan) {
                                        'ml' => 'ml',
                                        default => 'gram',
                                    }),
                            ])
                            ->columns(2)
                    ])
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

    public static function getNavigationGroup(): ?string
    {
        return 'Produksi';
    }
}
