<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistribusiResource\Pages;
// use App\Filament\Resources\DistribusiResource\RelationManagers;
use App\Models\Distribusi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
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
                Forms\Components\DatePicker::make('tanggal'),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Forms\Components\Select::make('reseller_id')
                    ->relationship('reseller', 'nama_reseller')
                    ->required(),

                Forms\Components\Repeater::make('distribusi_detail')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('produk_id')
                            ->relationship('produk', 'nama_produk'),

                        Forms\Components\TextInput::make('jumlah')
                            ->numeric(),

                        Forms\Components\TextInput::make('harga')
                            ->numeric(),
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
            'index' => Pages\ListDistribusis::route('/'),
            'create' => Pages\CreateDistribusi::route('/create'),
            'edit' => Pages\EditDistribusi::route('/{record}/edit'),
        ];
    }
}
