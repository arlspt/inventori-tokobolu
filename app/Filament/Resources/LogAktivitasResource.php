<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Models\LogAktivitas;

use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;

use Illuminate\Support\Facades\Auth;

use Filament\Tables\Columns\TextColumn;

use App\Filament\Resources\LogAktivitasResource\Pages;

class LogAktivitasResource extends Resource
{
    protected static ?string $model =
    LogAktivitas::class;

    protected static ?string $navigationIcon =
    'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel =
    'Log Aktivitas';

    protected static ?string $pluralModelLabel =
    'Log Aktivitas';

    protected static ?int $navigationSort =
    7;

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasRole('admin')
            ?? false;
    }

    public static function form(
        Form $form
    ): Form {
        return $form
            ->schema([]);
    }

    public static function table(
        Table $table
    ): Table {

        return $table

            ->defaultSort(
                'created_at',
                'desc'
            )

            ->searchPlaceholder(
                'Cari aktivitas...'
            )

            ->columns([

                TextColumn::make('user.name')

                    ->label('Dilakukan Oleh')

                    ->getStateUsing(function ($record) {

                        if (!$record->user) {
                            return '-';
                        }

                        $role =
                            $record->user
                            ->roles
                            ->first()?->name;

                        $role =
                            match ($role) {

                                'admin'
                                => 'Admin',

                                'karyawan'
                                => 'Karyawan',

                                default
                                => '-',
                            };

                        return
                            $record->user->name
                            . ' (' . $role . ')';
                    })

                    ->searchable(
                        query: function (
                            $query,
                            $search
                        ) {

                            $query
                                ->whereHas(
                                    'user',
                                    fn($q) =>
                                    $q->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    )
                                );
                        }
                    ),

                TextColumn::make(
                    'modul'
                )
                    ->label('Modul')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make(
                    'aktivitas'
                )
                    ->badge()
                    ->color(
                        fn($state) =>
                        match ($state) {
                            'Tambah'
                            => 'success',

                            'Ubah'
                            => 'warning',

                            'Hapus'
                            => 'danger',

                            default
                            => 'gray'
                        }
                    ),

                TextColumn::make(
                    'deskripsi'
                )
                    ->label('Aktivitas')
                    ->wrap(),

                TextColumn::make(
                    'created_at'
                )
                    ->label('Waktu')
                    ->dateTime(
                        'd M Y • H:i'
                    )
                    ->sortable(),
            ])

            ->filters([

                Tables\Filters\SelectFilter::make(
                    'aktivitas'
                )
                    ->label('Aktivitas')

                    ->placeholder('Semua')

                    ->options([

                        'Tambah'
                        => 'Tambah',

                        'Ubah'
                        => 'Ubah',

                        'Hapus'
                        => 'Hapus',
                    ])
            ])

            ->actions([])

            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [

            'index'
            =>
            Pages\ListLogAktivitas::route(
                '/'
            ),
        ];
    }
}
