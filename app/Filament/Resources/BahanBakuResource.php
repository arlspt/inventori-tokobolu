<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BahanBakuResource\Pages;
// use App\Filament\Resources\BahanBakuResource\RelationManagers;
use App\Models\BahanBaku;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;

class BahanBakuResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = BahanBaku::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_bahan')
                    ->required(),

                TextInput::make('stok_input')
                    ->label('Stok')
                    ->numeric()
                    ->required(),
                // ->live()

                Select::make('satuan')
                    // ->options([
                    //     'kg' => 'Kilogram',
                    //     'gram' => 'Gram',
                    // ])
                    ->default('gram')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('stok')
                    ->label('Stok')
                    ->formatStateUsing(function ($state) {
                        if ($state >= 1000) {
                            return ($state / 1000) . ' kg';
                        }

                        return number_format($state / 1000, 2) . ' kg';
                    })
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
            'index' => Pages\ListBahanBakus::route('/'),
            'create' => Pages\CreateBahanBaku::route('/create'),
            'edit' => Pages\EditBahanBaku::route('/{record}/edit'),
        ];
    }
}