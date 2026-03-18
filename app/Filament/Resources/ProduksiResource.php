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
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProduksiResource extends Resource
{
    protected static ?string $model = Produksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Produksi';
    protected static ?string $pluralModelLabel = 'Produksi';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('tanggal')
                    ->required(),

                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::id()),

                Repeater::make('produksiDetail')
                    ->relationship()
                    ->schema([

                        Select::make('produk_id')
                            ->relationship('produk', 'nama_produk')
                            ->required(),

                        TextInput::make('jumlah_produksi')
                            ->label('Qty')
                            ->numeric()
                            ->required(),

                        TextInput::make('gagal')
                            ->numeric()
                            ->default(0)
                            ->label('Gagal')

                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Hari, Tanggal')
                    ->formatStateUsing(function ($state) {
                        return \Carbon\Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y');
                    }),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),

                TextColumn::make('produksiDetail')
                    ->formatStateUsing(fn($record) => $record->produksiDetail->count())
                    ->label('Jumlah Produk'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat Pada'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->infolist([

                        RepeatableEntry::make('produksiDetail')
                            ->label('Detail Produksi')
                            ->schema([
                                TextEntry::make('produk.nama_produk')
                                    ->label('Produk'),
                                TextEntry::make('jumlah_produksi')
                                    ->label('Qty'),
                                TextEntry::make('gagal')
                                    ->label('Produk Gagal'),
                            ])
                            ->columns(3),
                    ]),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->actionsColumnLabel('Aksi')
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
