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
use Illuminate\Support\Facades\Auth;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                Forms\Components\Select::make('distribusi_id')
                    ->relationship('distribusi', 'id'),
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Forms\Components\DatePicker::make('tanggal'),

                Forms\Components\Repeater::make('retur_detail')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('produk_id')
                            ->relationship('produk', 'nama_produk'),

                        Forms\Components\TextInput::make('jumlah')
                            ->numeric(),

                        Forms\Components\TextInput::make('alasan')
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
            'index' => Pages\ListReturs::route('/'),
            'create' => Pages\CreateRetur::route('/create'),
            'edit' => Pages\EditRetur::route('/{record}/edit'),
        ];
    }
}
