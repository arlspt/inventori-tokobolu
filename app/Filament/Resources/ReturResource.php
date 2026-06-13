<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturResource\Pages;
use App\Models\Retur;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as FormSection;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\Grid;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Support\Facades\Auth;

class ReturResource extends Resource
{
    protected static ?string $model = Retur::class;
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Retur';
    protected static ?string $pluralModelLabel = 'Retur';

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
        return $user->dapatAksesModul('retur');
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

    protected static function bolehAksiRetur(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) return false;
        return $user->hasRole('admin')
            || $user->dapatAksesModul('retur');
    }

    protected static function bolehCetak(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        return $user->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // DATA DISTRIBUSI
                FormSection::make('Data Distribusi')
                    ->schema([
                        TextInput::make('distribusi_info')
                            ->label('Tujuan Distribusi')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('tanggal_distribusi')
                            ->label('Tanggal Distribusi')
                            ->readOnly()
                            ->dehydrated(false),

                        TextInput::make('nomor_invoice')
                            ->label('Nomor Invoice Distribusi')
                            ->readOnly()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($set, $get) {
                                $distribusiId = $get('distribusi_id');
                                if (!$distribusiId) {
                                    $set('nomor_invoice_distribusi', '-');
                                    return;
                                }
                                $distribusi = \App\Models\Distribusi::find($distribusiId);
                                $set(
                                    'nomor_invoice_distribusi',
                                    $distribusi?->nomor_invoice ?? '-'
                                );
                            }),
                    ])
                    ->columns(3),

                // DATA RETUR
                FormSection::make('Data Retur')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('tanggal')
                            ->label('Tanggal Retur')
                            ->default(now())
                            ->readOnly()
                            ->dehydrated(true)
                            ->required(),

                        TextInput::make('nomor_retur')
                            ->label('Nomor Retur')
                            ->readOnly()
                            ->dehydrated(false)
                            ->afterStateHydrated(function ($set) {
                                $year = now()->year;
                                $last = Retur::withTrashed()
                                    ->whereYear('created_at', $year)
                                    ->orderBy('id', 'desc')
                                    ->first();
                                $number = $last
                                    ? (int) substr($last->nomor_retur, -4) + 1
                                    : 1;
                                $preview = 'RET-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
                                $set('nomor_retur', $preview);
                            }),

                        Hidden::make('distribusi_id'),
                        Hidden::make('user_id'),

                        Textarea::make('keterangan')
                            ->label('Keterangan')
                            ->columnSpanFull(),
                    ]),

                // DETAIL RETUR
                FormSection::make('Detail Retur')
                    ->schema([
                        Repeater::make('detail')
                            ->label('Daftar Varian Retur')
                            ->relationship()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columns(2)
                            ->schema([

                                // KOLOM KIRI
                                Grid::make(1)
                                    ->schema([
                                        Select::make('produk_id')
                                            ->relationship('produk', 'nama_produk')
                                            ->disabled()
                                            ->dehydrated()
                                            ->native(false)
                                            ->label('Varian'),

                                        TextInput::make('jumlah')
                                            ->label('Jumlah Retur')
                                            ->numeric()
                                            ->minValue(0)
                                            ->required()
                                            ->reactive()
                                            ->live()
                                            ->helperText(function ($get) {
                                                $produkId     = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id');

                                                if (!$produkId || !$distribusiId) return null;

                                                $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                    ->where('produk_id', $produkId)
                                                    ->first();

                                                if (!$dist) return null;

                                                $jumlahAwal = $dist->jumlah;

                                                $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId)
                                                        ->whereNull('deleted_at');
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                // edit mode
                                                $currentId = $get('id');
                                                if ($currentId) {
                                                    $existing = \App\Models\ReturDetail::find($currentId);
                                                    if ($existing) {
                                                        $totalRetur -= $existing->jumlah;
                                                    }
                                                }

                                                // max murni dari DB, tidak melibatkan input user
                                                $max = $jumlahAwal - $totalRetur;
                                                if ($max < 0) $max = 0;

                                                $jumlahInput = (int) $get('jumlah');
                                                $sisa = $max - $jumlahInput;
                                                if ($sisa < 0) $sisa = 0;

                                                return "Maksimal retur: {$max} | Sisa: {$sisa}";
                                            })
                                            ->rule(function ($get) {
                                                return function ($attribute, $value, $fail) use ($get) {

                                                    $produkId     = $get('produk_id');
                                                    $distribusiId = $get('../../distribusi_id');

                                                    if (!$produkId || !$distribusiId) return;

                                                    $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                        ->where('produk_id', $produkId)
                                                        ->first();

                                                    if (!$dist) return;

                                                    $jumlahAwal = $dist->jumlah;

                                                    $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                        $q->where('distribusi_id', $distribusiId)
                                                            ->whereNull('deleted_at');
                                                    })
                                                        ->where('produk_id', $produkId)
                                                        ->sum('jumlah');

                                                    $currentId = $get('id');
                                                    if ($currentId) {
                                                        $existing = \App\Models\ReturDetail::find($currentId);
                                                        if ($existing) {
                                                            $totalRetur -= $existing->jumlah;
                                                        }
                                                    }

                                                    $max = $jumlahAwal - $totalRetur;
                                                    if ($max < 0) $max = 0;

                                                    if ($value > $max) {
                                                        $fail("Maksimal retur: $max");
                                                    }
                                                };
                                            })
                                            ->afterStateUpdated(function ($state, $get, $set) {

                                                $produkId     = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id');

                                                if (!$produkId || !$distribusiId) return;

                                                $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                    ->where('produk_id', $produkId)
                                                    ->first();

                                                if (!$dist) return;

                                                $jumlahAwal = $dist->jumlah;

                                                // query fresh langsung dari DB
                                                $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId)
                                                        ->whereNull('deleted_at');
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                // edit mode: kecualikan record ini
                                                $currentId = $get('id');
                                                if ($currentId) {
                                                    $existing = \App\Models\ReturDetail::find($currentId);
                                                    if ($existing) {
                                                        $totalRetur -= $existing->jumlah;
                                                    }
                                                }

                                                // max dihitung TANPA melibatkan $state sama sekali
                                                $max = $jumlahAwal - $totalRetur;
                                                if ($max < 0) $max = 0;

                                                if ((int) $state > $max) {
                                                    $set('jumlah', $max);

                                                    Notification::make()
                                                        ->title('Jumlah melebihi batas')
                                                        ->body("Maksimal retur hanya $max")
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                    ])
                                    ->columnSpan(1),

                                // KOLOM KANAN
                                Grid::make(1)
                                    ->schema([
                                        Select::make('alasan')
                                            ->label('Alasan')
                                            ->options([
                                                'rusak'           => 'Barang Rusak',
                                                'expired'         => 'Expired',
                                                'salah_kirim'     => 'Salah Kirim',
                                                'lainnya'         => 'Lainnya',
                                            ])
                                            ->placeholder('Pilih alasan')
                                            ->native(false)
                                            ->required(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->visible(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->dehydrated(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->reactive(),

                                        Textarea::make('alasan_lain')
                                            ->label('Alasan Lainnya')
                                            ->placeholder('Masukkan alasan lainnya')
                                            ->visible(
                                                fn($get) => ($get('jumlah') ?? 0) > 0 &&
                                                    $get('alasan') === 'lainnya'
                                            )
                                            ->required(
                                                fn($get) => ($get('jumlah') ?? 0) > 0 && $get('alasan') === 'lainnya'
                                            ),
                                    ])
                                    ->columnSpan(1),

                                Hidden::make('max_jumlah'),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->with(['detail.produk', 'distribusi.reseller', 'user'])
                    ->withTrashed()
                    ->orderByRaw('CASE WHEN deleted_at IS NULL THEN 0 ELSE 1 END ASC')
                    ->orderBy('created_at', 'desc')
            )
            ->searchPlaceholder('Cari Distribusi...')
            ->recordClasses(
                fn($record) =>
                $record->deleted_at ? 'opacity-70 text-gray-700' : null
            )
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status_retur')
                    ->label('Status Retur')
                    ->placeholder('Semua')
                    ->options([
                        'aktif'      => 'Aktif',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === '') {
                            return $query;
                        }
                        return match ($data['value']) {
                            'aktif'      => $query->whereNull('deleted_at'),
                            'dibatalkan' => $query->onlyTrashed(),
                            default      => $query,
                        };
                    }),
            ])
            ->recordUrl(null)
            ->recordAction('view')
            ->columns([
                TextColumn::make('nomor_retur')
                    ->label('No. Retur')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->formatStateUsing(
                        fn($state) =>
                        Carbon::parse($state)
                            ->locale('id')
                            ->translatedFormat('l, d F Y')
                    ),

                TextColumn::make('distribusi_tujuan')
                    ->label('Distribusi')
                    ->getStateUsing(
                        fn($record) =>
                        $record->distribusi->reseller
                            ? $record->distribusi->reseller->nama_reseller
                            : $record->distribusi->tujuan_lain
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->whereHas('distribusi', function ($q) use ($search) {
                            $q->whereHas('reseller', fn($q) => $q->where('nama_reseller', 'like', "%{$search}%"))
                                ->orWhere('tujuan_lain', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('total_retur')
                    ->label('Total Retur')
                    ->alignCenter()
                    ->getStateUsing(fn($record) => $record->detail->sum('jumlah')),

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
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn($record) => $record->deleted_at ? 'Dibatalkan' : 'Aktif')
                    ->badge()
                    ->colors([
                        'success' => fn($state) => $state === 'Aktif',
                        'danger'  => fn($state) => $state === 'Dibatalkan',
                    ]),
            ])

            // REKAP BULANAN DI HEADER TABLE
            ->headerActions([
                \Filament\Tables\Actions\Action::make('rekap_bulanan_retur')
                    ->visible(fn() => static::bolehCetak())
                    ->label('Rekap Bulanan Retur')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('gray')
                    ->form([

                        // ── TOGGLE TIPE TUJUAN ──
                        Select::make('tipe_tujuan')
                            ->label('Tipe Tujuan')
                            ->options([
                                'reseller'    => 'Reseller',
                                'tujuan_lain' => 'Customer',
                            ])
                            ->required()
                            ->placeholder('Pilih tipe tujuan')
                            ->native(false)
                            ->live(),

                        // ── PILIH RESELLER (hanya muncul kalau tipe = reseller) ──
                        Select::make('reseller_id')
                            ->label('Reseller')
                            ->options(\App\Models\Reseller::pluck('nama_reseller', 'id'))
                            ->searchable()
                            ->placeholder('Pilih Reseller')
                            ->visible(fn($get) => $get('tipe_tujuan') === 'reseller')
                            ->required(fn($get) => $get('tipe_tujuan') === 'reseller')
                            ->live(),

                        // ── PILIH BULAN ──
                        Select::make('bulan')
                            ->label('Bulan')
                            ->required()
                            ->placeholder('Pilih bulan')
                            ->native(false)
                            ->visible(fn($get) => filled($get('tipe_tujuan')))
                            ->options(function ($get) {
                                $tipe = $get('tipe_tujuan');
                                if (!$tipe) return [];

                                if ($tipe === 'reseller') {
                                    $resellerId = $get('reseller_id');
                                    if (!$resellerId) return [];

                                    // bulan yang ada retur aktif untuk reseller ini
                                    return Retur::whereNull('deleted_at')
                                        ->whereHas('distribusi', fn($q) => $q->where('reseller_id', $resellerId))
                                        ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan_key')
                                        ->distinct()
                                        ->orderByRaw('bulan_key DESC')
                                        ->pluck('bulan_key')
                                        ->mapWithKeys(fn($b) => [
                                            $b => Carbon::createFromFormat('Y-m', $b)
                                                ->locale('id')
                                                ->translatedFormat('F Y')
                                        ])
                                        ->toArray();
                                }

                                // tujuan_lain: bulan yang ada retur aktif dari tujuan lain
                                return Retur::whereNull('deleted_at')
                                    ->whereHas('distribusi', fn($q) => $q->whereNotNull('tujuan_lain'))
                                    ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan_key')
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
                            ->live(),
                    ])
                    ->action(function (array $data) {
                        return redirect(route('retur.rekap-bulanan', [
                            'tipe_tujuan' => $data['tipe_tujuan'],
                            'reseller_id' => $data['reseller_id'] ?? null,
                            'bulan'       => $data['bulan'],
                        ]));
                    })
                    ->modalHeading('Cetak Rekap Bulanan Retur')
                    ->modalSubmitActionLabel('Cetak')
                    ->modalCancelActionLabel('Batal'),
            ])

            ->actions([
                ActionGroup::make([
                    // VIEW DETAIL
                    Tables\Actions\ViewAction::make()
                        ->label('View')
                        ->color('info')
                        ->modalFooterActions([
                            \Filament\Tables\Actions\Action::make('cetak_dari_view')
                                ->visible(fn() => static::bolehCetak())
                                ->label('Cetak Retur')
                                ->icon('heroicon-o-printer')
                                ->color('gray')
                                ->url(fn($record) => route('retur.cetak', $record->id))
                                ->openUrlInNewTab()
                                ->visible(fn() => static::bolehCetak()),
                        ])
                        ->infolist([
                            InfoSection::make('Data Distribusi')
                                ->schema([
                                    TextEntry::make('distribusi.nomor_invoice')
                                        ->label('No. Invoice Distribusi'),
                                    TextEntry::make('distribusi_id')
                                        ->label('Distribusi')
                                        ->formatStateUsing(
                                            fn($record) =>
                                            $record->distribusi->reseller
                                                ? $record->distribusi->reseller->nama_reseller
                                                : $record->distribusi->tujuan_lain
                                        ),
                                    TextEntry::make('distribusi.tanggal')
                                        ->label('Tanggal Distribusi')
                                        ->date('d F Y'),
                                ])->columns(3),

                            InfoSection::make('Data Retur')
                                ->schema([
                                    TextEntry::make('nomor_retur')
                                        ->label('Nomor Retur'),
                                    TextEntry::make('tanggal')
                                        ->label('Tanggal Retur')
                                        ->date('d F Y'),
                                    TextEntry::make('keterangan')
                                        ->label('Keterangan')
                                        ->placeholder('-'),
                                ])->columns(3),

                            InfoSection::make('Daftar Varian Retur')
                                ->schema([
                                    \Filament\Infolists\Components\View::make('infolists.components.retur-detail-table')
                                        ->viewData(fn($record) => ['detail' => $record->detail])
                                ]),
                        ]),

                    // ✅ UBAH
                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),

                    // ✅ CETAK RETUR
                    Tables\Actions\Action::make('cetak_retur')
                        ->visible(fn() => static::bolehCetak())
                        ->label('Cetak Retur')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->url(fn($record) => route('retur.cetak', $record->id))
                        ->openUrlInNewTab(),

                    // ✅ BATAL RETUR
                    Tables\Actions\Action::make('batal')
                        ->label('Batal Retur')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Batal Retur?')
                        ->modalDescription('Tindakan ini akan membatalkan retur. Pastikan tidak ada retur yang terkait dengan distribusi ini sebelum membatalkannya.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->modalCancelActionLabel('Tidak')
                        ->action(fn($record) => $record->delete())
                        ->visible(
                            fn($record) =>
                            $record->deleted_at === null
                                && static::bolehCetak()
                        ),
                ])->color('black'),
            ])
            ->actionsColumnLabel('Aksi');
    }

    public static function getRelations(): array
    {
        return [];
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
