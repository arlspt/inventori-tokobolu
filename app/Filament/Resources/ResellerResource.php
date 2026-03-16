<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResellerResource\Pages;
// use App\Filament\Resources\ResellerResource\RelationManagers;
use App\Models\Reseller;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResellerResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Reseller::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_reseller')->required(),

                Forms\Components\TextInput::make('alamat'),

                Forms\Components\TextInput::make('no_telp'),
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
            'index' => Pages\ListResellers::route('/'),
            'create' => Pages\CreateReseller::route('/create'),
            'edit' => Pages\EditReseller::route('/{record}/edit'),
        ];
    }
}
