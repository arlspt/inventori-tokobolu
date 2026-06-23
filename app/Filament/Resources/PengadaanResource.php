<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PengadaanResource\Pages;
use App\Models\Pengadaan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\ActionGroup;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Carbon\Carbon;

class PengadaanResource extends Resource
{
    protected static ?string $model = Pengadaan::class;
    protected static ?int $navigationSort = 1; // Urutan 1 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Pengadaan Bahan Baku';
    protected static ?string $modelLabel = 'Pengadaan';
    protected static ?string $pluralModelLabel = 'Pengadaan Bahan Baku';

    // Cek akses navigasi (apakah modul muncul di sidebar)
    public static function canAccess(): bool
    {
        /** @var \App\Models\User|null $user */

        $user = Auth::user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        // karyawan selalu bisa lihat (navigasi tetap muncul)
        return $user->hasRole('karyawan');
    }

    // Karyawan tidak bisa create kalau modul tidak diizinkan
    public static function canCreate(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        if ($user->hasRole('admin')) return true;
        return $user->dapatAksesModul('pengadaan');
    }

    // Karyawan tidak pernah bisa edit
    public static function canEdit($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasRole('admin');
    }

    // Karyawan tidak pernah bisa delete
    public static function canDelete($record): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Form Data Pengadaan')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(fn() => Auth::id()),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih Supplier')
                            ->required()
                            // FORM TAMBAH SUPPLIER
                            ->createOptionForm([
                                TextInput::make('nama_supplier')
                                    ->label('Nama Supplier')
                                    ->required(),
                                TextInput::make('telepon')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required(),
                                Textarea::make('alamat')
                                    ->label('Alamat')
                                    ->required(),
                                Select::make('kota')
                                    ->label('Kota')
                                    ->placeholder('Pilih Kota')
                                    ->options(\App\Helpers\KotaIndonesia::list())
                                    ->searchable()
                                    ->nullable(),
                            ])
                            // CUSTOM BUTTON
                            ->createOptionAction(function ($action) {
                                return $action
                                    ->label('Tambah Supplier')
                                    ->modalHeading('Tambah Supplier Baru')
                                    ->modalSubmitActionLabel('Simpan')
                                    ->modalCancelActionLabel('Batal');
                            }),
                    ]),
                Section::make('Detail Bahan')
                    ->schema([
                        Repeater::make('pengadaanDetail')
                            ->relationship()
                            ->columns(4)
                            ->deleteAction(
                                fn($action) => $action
                                    ->label('Hapus')
                                    ->icon('heroicon-o-trash')
                                    ->color('danger')
                                    ->before(function ($record) {
                                        if (!$record) return;
                                        $bahan = \App\Models\BahanBaku::find($record->bahan_baku_id);
                                        if ($bahan) {
                                            $bahan->decrement('stok', $record->jumlah);
                                        }
                                    })
                            )
                            ->schema([
                                // BARIS 1
                                Select::make('bahan_baku_id')
                                    ->label('Bahan Baku')
                                    ->placeholder('Pilih Bahan Baku')
                                    ->relationship('bahanBaku', 'nama_bahan')
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->columnSpan(2)
                                    ->createOptionForm([
                                        TextInput::make('nama_bahan')
                                            ->label('Nama Bahan')
                                            ->required(),

                                        Select::make('satuan')
                                            ->label('Satuan')
                                            ->placeholder('Pilih Satuan')
                                            ->options([
                                                'gram' => 'Gram (Padat)',
                                                'ml'   => 'Mililiter (Cair)',
                                            ])
                                            ->required(),
                                        TextInput::make('stok')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled()
                                            ->dehydrated()
                                            ->hidden(),
                                    ])
                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->label('Tambah Bahan')
                                            ->modalHeading('Tambah Bahan Baku')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    })
                                    ->afterStateUpdated(function ($state, $set) {
                                        $bahan = \App\Models\BahanBaku::find($state);
                                        if (!$bahan) return;
                                        $set('satuan_input', match ($bahan->satuan) {
                                            'gram'  => 'kg',
                                            'ml'    => 'liter',
                                            default => 'gram',
                                        });
                                    })
                                    ->required(),
                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->step(0.1)
                                    ->postfix(function ($get) {
                                        return match ($get('satuan_input')) {
                                            'kg'    => 'Kg',
                                            'liter' => 'L',
                                            'ml'    => 'ml',
                                            'gram'  => 'gr',
                                            default => '',
                                        };
                                    })
                                    ->required()
                                    ->live(debounce: 700)
                                    ->columnSpan(1)
                                    ->afterStateHydrated(function (
                                        $state,
                                        $set,
                                        $get
                                    ) {

                                        if ($state === null) {
                                            return;
                                        }

                                        $satuan =
                                            $get('satuan_input')
                                            ?? 'kg';

                                        if (
                                            in_array(
                                                $satuan,
                                                ['kg', 'liter']
                                            )
                                        ) {

                                            $set(
                                                'jumlah',
                                                $state / 1000
                                            );

                                            return;
                                        }

                                        $set(
                                            'jumlah',
                                            $state
                                        );
                                    })
                                    ->dehydrateStateUsing(function ($state, $get) {
                                        return match ($get('satuan_input')) {
                                            'kg', 'liter' => round($state * 1000),
                                            default       => round($state),
                                        };
                                    })
                                    ->afterStateUpdated(fn($get, $set) => self::hitungSubtotal($get, $set)),

                                Select::make('satuan_input')
                                    ->label('Satuan')
                                    ->options(function ($get) {
                                        $bahan = \App\Models\BahanBaku::find($get('bahan_baku_id'));
                                        if (!$bahan) return [];
                                        return match ($bahan->satuan) {
                                            'gram'  => ['kg' => 'Kilogram', 'gram' => 'Gram'],
                                            'ml'    => ['liter' => 'Liter', 'ml' => 'Mililiter'],
                                            default => ['kg' => 'Kilogram'],
                                        };
                                    })
                                    ->live()
                                    ->columnSpan(1)
                                    ->native(false)
                                    ->afterStateUpdated(function ($state, $old, $get, $set) {
                                        $jumlah = (float) ($get('jumlah') ?? 0);
                                        $harga  = (float) ($get('harga') ?? 0);

                                        // Besar → kecil
                                        if (
                                            in_array($old, ['kg', 'liter']) &&
                                            in_array($state, ['gram', 'ml'])
                                        ) {
                                            $set('jumlah', $jumlah * 1000);
                                            $set('harga', $harga / 1000);
                                        }

                                        // Kecil → besar
                                        elseif (
                                            in_array($old, ['gram', 'ml']) &&
                                            in_array($state, ['kg', 'liter'])
                                        ) {
                                            $set('jumlah', $jumlah / 1000);
                                            $set('harga', $harga * 1000);
                                        }

                                        self::hitungSubtotal($get, $set);
                                    })
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        if ($state) {
                                            return;
                                        }
                                        $bahan =
                                            \App\Models\BahanBaku::find(
                                                $get('bahan_baku_id')
                                            );
                                        if (!$bahan) {
                                            return;
                                        }
                                        $set(
                                            'satuan_input',
                                            match ($bahan->satuan) {
                                                'gram' => 'kg',
                                                'ml' => 'liter',
                                                default => 'kg',
                                            }
                                        );
                                    })
                                    ->placeholder('Kilogram / Liter')
                                    ->required(),

                                // BARIS 2
                                TextInput::make('harga')
                                    ->label('Harga')
                                    ->prefix('Rp')
                                    ->numeric(false)
                                    ->required()
                                    ->live(debounce: 700)
                                    ->columnSpan(2)
                                    ->afterStateHydrated(function ($state, $set, $get) {
                                        if ($state === null) return;
                                        // ✅ konversi harga per gram/ml ke per kg/liter untuk tampilan
                                        $satuan = $get('satuan_input') ?? 'kg';
                                        $set('harga', match ($satuan) {
                                            'kg', 'liter' => (float) $state * 1000,
                                            default        => (float) $state,
                                        });
                                    })
                                    ->dehydrateStateUsing(function ($state, $get) {
                                        // ✅ konversi harga per kg/liter ke per gram/ml untuk DB
                                        $satuan = $get('satuan_input');
                                        return match ($satuan) {
                                            'kg', 'liter' => round((float) $state / 1000, 4),
                                            default        => round((float) $state),
                                        };
                                    })
                                    ->placeholder(function ($get) {
                                        return match ($get('satuan_input')) {
                                            'kg'    => 'Masukan harga per 1 Kilogram',
                                            'liter' => 'Masukan harga per 1 Liter',
                                            'ml'    => 'Masukan harga per Mililiter',
                                            'gram'  => 'Masukan harga per Gram',
                                            default => '',
                                        };
                                    })
                                    ->afterStateUpdated(fn($get, $set) => self::hitungSubtotal($get, $set)),

                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('Rp')
                                    ->columnSpan(2) // ✅ setengah lebar
                                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.')),
                            ])
                            ->addActionLabel('Tambah Bahan')
                            ->addAction(
                                fn($action) => $action
                                    ->label('Tambah Bahan')
                                    ->icon('heroicon-m-plus')
                            )
                    ]),
            ]);
    }

    private static function hitungSubtotal($get, $set): void
    {
        $jumlah  = (float) ($get('jumlah') ?? 0); // dalam tampilan (kg/liter/gram/ml)
        $harga   = (float) ($get('harga') ?? 0);  // harga per satuan tampilan
        $satuan  = $get('satuan_input');

        // ✅ konversi jumlah tampilan ke satuan dasar (gram/ml) untuk simpan ke DB
        $jumlahDasar = match ($satuan) {
            'kg', 'liter' => $jumlah * 1000,
            default        => $jumlah,
        };

        // ✅ konversi harga per kg/liter ke harga per gram/ml untuk DB
        $hargaDasar = match ($satuan) {
            'kg', 'liter' => $harga / 1000,
            default        => $harga,
        };

        // subtotal = jumlah_dasar * harga_dasar
        $set('subtotal', round($jumlahDasar * $hargaDasar));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(['pengadaanDetail.bahanBaku', 'supplier', 'user'])
                    ->orderBy('tanggal', 'desc')        // terbaru di atas
                    ->orderBy('created_at', 'desc')     // tiebreaker
            )
            ->recordUrl(null)
            ->recordAction('view') // klik row -> modal
            ->searchPlaceholder('Cari Supplier...')
            ->columns([
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                    ),
                TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable(),
                TextColumn::make('jumlah_item')
                    ->label('Jumlah Bahan')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => $record->pengadaanDetail->count()),
                TextColumn::make('total_harga')
                    ->label('Total')
                    ->alignEnd()
                    ->getStateUsing(
                        fn($record) =>
                        'Rp. ' . number_format($record->pengadaanDetail->sum('subtotal'), 0, ',', '.')
                    ),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->formatStateUsing(function ($state, $record) {
                        $role = $record->user?->roles->first()?->name;
                        $roleLabel = match ($role) {
                            'admin' => 'Admin',
                            'karyawan' => 'Karyawan',
                            default => ucfirst($role ?? '-'),
                        };
                        return $state . ' (' . $roleLabel . ')';
                    })
                    ->searchable()
                    ->alignRight(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier', 'nama_supplier')
                    ->placeholder('Semua Supplier'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Dibuat Oleh')
                    ->options(
                        \App\Models\User::all()->mapWithKeys(function ($user) {
                            $role = $user->getRoleNames()->first() ?? '-';
                            return [$user->id => $user->name . ' (' . ucfirst($role) . ')'];
                        })
                    )
                    ->placeholder('Semua User'),
                Tables\Filters\Filter::make('bulan')
                    ->form([
                        Select::make('bulan')
                            ->label('Bulan')
                            ->options(function () {
                                return Pengadaan::selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan_key')
                                    ->distinct()
                                    ->orderByRaw('bulan_key DESC')
                                    ->pluck('bulan_key')
                                    ->mapWithKeys(fn($b) => [
                                        $b => Carbon::createFromFormat('Y-m', $b)
                                            ->locale('id')
                                            ->translatedFormat('F Y')
                                    ])
                                    ->toArray();
                            })
                            ->placeholder('Semua Bulan'),
                    ])
                    ->query(function ($query, array $data) {
                        if (!filled($data['bulan'])) return $query;
                        [$year, $month] = explode('-', $data['bulan']);
                        return $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
                    })
                    ->indicateUsing(function (array $data) {
                        if (!filled($data['bulan'])) return null;
                        return 'Bulan: ' . Carbon::createFromFormat('Y-m', $data['bulan'])
                            ->locale('id')->translatedFormat('F Y');
                    }),
            ])
            ->actions([
                // DROPDOWN ACTION
                ActionGroup::make([
                    ViewAction::make('view')
                        ->modalHeading('Detail Pengadaan')
                        ->label('Detail')
                        ->color('info')
                        ->infolist([
                            // SECTION INFORMASI
                            InfoSection::make('Informasi Pengadaan')
                                ->schema([
                                    TextEntry::make('tanggal')
                                        ->label('Tanggal')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            Carbon::parse($state)
                                                ->locale('id')
                                                ->translatedFormat('l, d F Y')
                                        ),
                                    TextEntry::make('supplier.nama_supplier')
                                        ->label('Supplier'),
                                    TextEntry::make('user.name')
                                        ->label('Dibuat Oleh'),
                                ])
                                ->columns(3),
                            // SECTION DETAIL BAHAN
                            InfoSection::make('Detail Bahan')
                                ->schema([
                                    \Filament\Infolists\Components\View::make('infolists.components.pengadaan-detail-table')
                                        ->viewData(fn($record) => ['detail' => $record->pengadaanDetail->load('bahanBaku')])
                                ]),
                        ]),
                    Tables\Actions\EditAction::make()->label('Ubah'),
                    Tables\Actions\DeleteAction::make()
                        ->modalHeading('Hapus Pengadaan Bahan Baku')
                        ->modalDescription('Tindakan ini akan mengurangi stok bahan baku yang dihapus.')
                        ->modalSubmitActionLabel('Ya, Hapus')
                        ->modalCancelActionLabel('Batal'),
                ])
                    ->color('black'),
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
            'index' => Pages\ListPengadaans::route('/'),
            'create' => Pages\CreatePengadaan::route('/create'),
            'edit' => Pages\EditPengadaan::route('/{record}/edit'),
        ];
    }
}