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
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Builder;

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
        /** @var User|null $user */
        $user = Auth::user();

        return $user?->hasRole('admin') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // admin-key boleh edit semua
        if ($user->isAdminKey()) {
            return true;
        }

        // admin biasa hanya boleh edit karyawan
        if (
            $user->hasRole('admin')
            && $record->hasRole('karyawan')
        ) {
            return true;
        }
        return false;
    }

    public static function canManageUser($record): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // admin-key → boleh semua
        if ($user->isAdminKey()) {
            return true;
        }

        // admin biasa → hanya karyawan
        return $record->hasRole('karyawan');
    }

    public static function canDelete($record): bool
    {
        if (static::adalahAdminKey($record)) {
            return false;
        }

        return static::canManageUser($record);
    }

    protected static function adalahAdminKey($record): bool
    {
        return $record
            && $record->hasRole('admin')
            && $record->isAdminKey();
    }

    protected static function adminBiasa(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        return $user
            && $user->hasRole('admin')
            && !$user->isAdminKey();
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
                            ->relationship(
                                name: 'roles',
                                titleAttribute: 'name'
                            )
                            ->preload()
                            ->searchable()
                            ->native(false)
                            ->required()
                            ->live()

                            // admin biasa → otomatis karyawan
                            ->default(
                                fn() =>
                                static::adminBiasa()
                                    ? 'karyawan'
                                    : null
                            )

                            // sembunyikan role
                            ->visible(
                                fn() =>
                                !static::adminBiasa()
                            )

                            ->dehydrated(true)
                            ->placeholder('Pilih Role')
                    ]),

                // ✅ AKSES MODUL — hanya muncul kalau role = karyawan
                Section::make('Akses Modul')
                    ->description(
                        'Modul yang dicentang, karyawan dapat melihat dan menambahkan data. Modul yang tidak dicentang, karyawan hanya dapat melihat.'
                    )
                    ->visible(function ($get) {

                        // admin biasa → selalu tampil
                        if (static::adminBiasa()) {
                            return true;
                        }

                        $role = $get('roles');

                        if (is_array($role)) {
                            $role = $role[0] ?? null;
                        }

                        return $role === 'karyawan'
                            || $role == 2;
                    })
                    ->schema([
                        CheckboxList::make('modul_akses')
                            ->label('')
                            ->options([
                                'pengadaan'  => 'Pengadaan Bahan Baku',
                                'produksi'   => 'Produksi',
                                'distribusi' => 'Distribusi',
                                'retur'      => 'Retur',
                            ])
                            ->columns(2)

                            ->afterStateHydrated(function ($set, $record) {

                                if (!$record) {
                                    return;
                                }

                                $aktif = $record->modulePermissions
                                    ->where('dapat_akses', true)
                                    ->pluck('modul')
                                    ->toArray();

                                $set('modul_akses', $aktif);
                            })

                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        /** @var User|null $user */
        $user = Auth::user();

        if (!$user) {
            return $query->whereRaw('1=0');
        }

        // Admin Key → lihat semua
        if ($user->isAdminKey()) {
            return $query;
        }

        // Admin biasa → hanya lihat karyawan
        return $query->whereHas(
            'roles',
            fn($q) =>
            $q->where('name', 'karyawan')
        );
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Cari Nama...')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(query: function ($query, $search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    }),

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

                // ✅ tampilkan modul yang diizinkan
                TextColumn::make('modul_diizinkan')
                    ->label('Akses Modul')
                    ->getStateUsing(function ($record) {
                        if ($record->hasRole('admin')) return 'Semua Modul';

                        $modul = $record->modulePermissions
                            ->where('dapat_akses', true)
                            ->pluck('modul')
                            ->map(fn($m) => match ($m) {
                                'pengadaan'  => 'Pengadaan',
                                'produksi'   => 'Produksi',
                                'distribusi' => 'Distribusi',
                                'retur'      => 'Retur',
                                default      => $m,
                            })
                            ->join(', ');

                        return $modul ?: 'Hanya Lihat';
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([

                // FILTER ROLE
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin' => 'Admin',
                        'karyawan' => 'Karyawan',
                    ])
                    ->visible(function () {
                        /** @var User|null $user */
                        $user = Auth::user();
                        return $user?->isAdminKey();
                    })
                    ->placeholder('Semua Role')
                    ->query(function ($query, array $data) {

                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas(
                            'roles',
                            fn($q) =>
                            $q->where('name', $data['value'])
                        );
                    }),

                // FILTER AKSES MODUL
                Tables\Filters\SelectFilter::make('akses_modul')
                    ->label('Akses Modul')
                    ->options([
                        'pengadaan'  => 'Pengadaan Bahan Baku',
                        'produksi'   => 'Produksi',
                        'distribusi' => 'Distribusi',
                        'retur'      => 'Retur',
                    ])
                    ->placeholder('Semua Modul')
                    ->query(function ($query, array $data) {
                        if (blank($data['value'])) {
                            return $query;
                        }
                        return $query->whereHas(
                            'modulePermissions',
                            fn($q) =>
                            $q->where('modul', $data['value'])
                                ->where('dapat_akses', true)
                        );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->visible(fn($record) => static::canEdit($record)),

                    // RESET PASSWORD BY ADMIN
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
                        ->visible(
                            fn($record) =>
                            $record->id !== Auth::id()
                                &&
                                !static::adalahAdminKey($record)
                                &&
                                static::canManageUser($record)
                        ),

                    Tables\Actions\DeleteAction::make()
                        ->label('Hapus')
                        ->visible(
                            fn($record) =>
                            $record->id !== Auth::id()
                                &&
                                !static::adalahAdminKey($record)
                                &&
                                static::canManageUser($record)
                        ),

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
