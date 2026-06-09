<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Manajemen User';
    protected static ?string $pluralModelLabel = 'User';

    // ✅ hanya admin yang bisa akses
    public static function canAccess(): bool
    {
        // type hint dengan casting untuk memastikan $user adalah instance User atau null
        /** @var User|null $user */ //Dengan @var docblock, VSCode tahu bahwa $user adalah instance User yang punya method hasRole()
        $user = Auth::user();

        return $user?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi User')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                            ->minLength(6)
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->helperText(
                                fn($livewire) => $livewire instanceof Pages\CreateUser
                                    ? null
                                    : 'Kosongkan jika tidak ingin mengubah password'
                            ),

                        Select::make('roles')
                            ->label('Role')
                            ->relationship('roles', 'name')
                            ->options([
                                'admin'    => 'Admin',
                                'karyawan' => 'Karyawan',
                            ])
                            ->native(false)
                            ->required()
                            ->placeholder('Pilih role'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'admin'    => 'Admin',
                        'karyawan' => 'Karyawan',
                        default    => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'admin'    => 'warning',
                        'karyawan' => 'info',
                        default    => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),

                    // ✅ RESET PASSWORD BY ADMIN
                    Tables\Actions\Action::make('reset_password')
                        ->label('Reset Password')
                        ->icon('heroicon-o-key')
                        ->color('warning')
                        ->form([
                            TextInput::make('new_password')
                                ->label('Password Baru')
                                ->password()
                                ->revealable()
                                ->required()
                                ->minLength(6)
                                ->confirmed(),

                            TextInput::make('new_password_confirmation')
                                ->label('Konfirmasi Password')
                                ->password()
                                ->revealable()
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $record->update([
                                'password' => Hash::make($data['new_password']),
                            ]);

                            Notification::make()
                                ->title('Password berhasil direset')
                                ->success()
                                ->send();
                        })
                        ->modalHeading('Reset Password')
                        ->modalSubmitActionLabel('Reset')
                        ->modalCancelActionLabel('Batal')
                        // ✅ tidak bisa reset password diri sendiri
                        ->visible(fn($record) => $record->id !== Auth::id()),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        // ✅ tidak bisa hapus diri sendiri
                        ->visible(fn($record) => $record->id !== Auth::id()),

                ])->color('black'),
            ])
            ->actionsColumnLabel('Aksi')
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
