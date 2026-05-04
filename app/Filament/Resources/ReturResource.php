<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReturResource\Pages;
// use App\Filament\Resources\ReturResource\RelationManagers;
use App\Models\Retur;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section as FormSection;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Forms\Components\Select;
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
use Filament\Infolists\Components\RepeatableEntry;


class ReturResource extends Resource
{
    protected static ?string $model = Retur::class;
    protected static ?int $navigationSort = 4; // Urutan 4 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';
    protected static ?string $navigationLabel = 'Retur';
    protected static ?string $pluralModelLabel = 'Retur';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // DATA DISTRIBUSI (HARUS DI ATAS)
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
                                            // ->options(\App\Models\Produk::pluck('nama_produk', 'id'))
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

                                                $produkId = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id');

                                                if (!$produkId || !$distribusiId) return null;

                                                $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                    ->where('produk_id', $produkId)
                                                    ->first();

                                                if (!$dist) return null;

                                                // 🔥 pakai jumlah_awal
                                                $jumlahAwal = $dist->jumlah_awal ?? $dist->jumlah;

                                                $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId)
                                                        ->whereNull('deleted_at');
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                // 🔥 EDIT MODE FIX (BIAR GAK MENTOK 0)
                                                $currentId = $get('id');

                                                if ($currentId) {
                                                    $existing = \App\Models\ReturDetail::find($currentId);
                                                    if ($existing) {
                                                        $totalRetur -= $existing->jumlah;
                                                    }
                                                }

                                                $max = $jumlahAwal - $totalRetur;

                                                if ($max < 0) $max = 0;

                                                return "Maksimal retur: $max";
                                            })
                                            ->rule(function ($get) {
                                                return function ($attribute, $value, $fail) use ($get) {

                                                    $produkId = $get('produk_id');
                                                    $distribusiId = $get('../../distribusi_id');

                                                    if (!$produkId || !$distribusiId) return;

                                                    $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                        ->where('produk_id', $produkId)
                                                        ->first();

                                                    if (!$dist) return;

                                                    $jumlahAwal = $dist->jumlah_awal ?? $dist->jumlah;

                                                    $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                        $q->where('distribusi_id', $distribusiId)
                                                            ->whereNull('deleted_at');
                                                    })
                                                        ->where('produk_id', $produkId)
                                                        ->sum('jumlah');

                                                    // 🔥 EDIT MODE FIX
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

                                                $produkId    = $get('produk_id');
                                                $distribusiId = $get('../../distribusi_id');

                                                if (!$produkId || !$distribusiId) return;

                                                $dist = \App\Models\DistribusiDetail::where('distribusi_id', $distribusiId)
                                                    ->where('produk_id', $produkId)
                                                    ->first();

                                                if (!$dist) return;

                                                $jumlahAwal = $dist->jumlah_awal ?? $dist->jumlah;

                                                $totalRetur = \App\Models\ReturDetail::whereHas('retur', function ($q) use ($distribusiId) {
                                                    $q->where('distribusi_id', $distribusiId)
                                                        ->whereNull('deleted_at');
                                                })
                                                    ->where('produk_id', $produkId)
                                                    ->sum('jumlah');

                                                // ✅ kurangi jumlah existing retur saat ini (edit mode)
                                                $currentId = $get('id');
                                                if ($currentId) {
                                                    $existing = \App\Models\ReturDetail::find($currentId);
                                                    if ($existing) {
                                                        $totalRetur -= $existing->jumlah;
                                                    }
                                                }

                                                $max = $jumlahAwal - $totalRetur;
                                                if ($max < 0) $max = 0;

                                                if ((int) $state > $max) {
                                                    $set('jumlah', $max); // ✅ auto-reset ke maksimal

                                                    Notification::make()
                                                        ->title('Jumlah melebihi batas')
                                                        ->body("Maksimal retur hanya $max")
                                                        ->warning()
                                                        ->send();
                                                }
                                            })
                                    ])
                                    ->columnSpan(1), // WAJIB

                                // KOLOM KANAN
                                Grid::make(2)
                                    ->schema([

                                        Select::make('alasan')
                                            ->label('Alasan')
                                            ->options([
                                                'rusak' => 'Barang Rusak',
                                                'expired' => 'Expired',
                                                'salah_kirim' => 'Salah Kirim',
                                                'retur_pelanggan' => 'Retur Pelanggan',
                                                'lainnya' => 'Lainnya',
                                            ])
                                            ->placeholder('Pilih alasan')
                                            ->native(false)
                                            ->required(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->visible(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->dehydrated(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->reactive(),

                                        Select::make('kondisi')
                                            ->label('Kondisi')
                                            ->options([
                                                'baik' => 'Baik (Layak Jual)',
                                                'rusak' => 'Rusak (Tidak Layak)',
                                            ])
                                            ->placeholder('Pilih kondisi')
                                            ->native(false)
                                            ->required()
                                            ->default('rusak')
                                            // ->required(fn($get) => ($get('jumlah') ?? 0) > 0)
                                            ->visible(fn($get) => ($get('jumlah') ?? 0) > 0)
                                        // ->dehydrated(fn($get) => ($get('jumlah') ?? 0) > 0)
                                        ,

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
                                    ->columnSpan(1), // WAJIB

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
            )
            ->recordClasses(
                fn($record) =>
                $record->deleted_at
                    ? 'opacity-70 text-gray-700'
                    : null
            )
            ->defaultSort('deleted_at', 'asc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status_retur')
                    ->label('Status Retur')
                    ->placeholder('Semua')
                    ->options([
                        'aktif' => 'Aktif',
                        'dibatalkan' => 'Dibatalkan',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === '') {
                            return $query; // semua
                        }
                        return match ($data['value']) {
                            'aktif' => $query->whereNull('deleted_at'),
                            'dibatalkan' => $query->onlyTrashed(),
                            default => $query,
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
                    ),

                TextColumn::make('total_retur')
                    ->label('Total Retur')
                    ->getStateUsing(
                        fn($record) =>
                        $record->detail->sum('jumlah')
                    ),

                TextColumn::make('user.name')
                    ->label('Dibuat Oleh'),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(
                        fn($record) =>
                        $record->deleted_at ? 'Dibatalkan' : 'Aktif'
                    )
                    ->badge()
                    ->colors([
                        'success' => fn($state) => $state === 'Aktif',
                        'danger' => fn($state) => $state === 'Dibatalkan',
                    ]),
            ])
            ->headerActions([])

            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View')
                        ->color('info')
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
                                    RepeatableEntry::make('detail')->label('Detail Varian Retur')
                                        ->schema([
                                            TextEntry::make('produk.nama_produk')->columnSpan(2)->label('Varian'),
                                            TextEntry::make('jumlah')->label('Jumlah Retur'),
                                            TextEntry::make('alasan')
                                                ->formatStateUsing(fn($state) => match ($state) {
                                                    'rusak' => 'Barang Rusak',
                                                    'expired' => 'Expired',
                                                    'salah_kirim' => 'Salah Kirim',
                                                    'retur_pelanggan' => 'Retur Pelanggan',
                                                    default => $state,
                                                }),
                                            TextEntry::make('kondisi')
                                                ->label('Kondisi')
                                                ->badge()
                                                ->color(fn($state) => match ($state) {
                                                    'baik' => 'success',
                                                    'rusak' => 'danger',
                                                })
                                                ->formatStateUsing(fn($state) => match ($state) {
                                                    'baik' => 'Baik',
                                                    'rusak' => 'Rusak',
                                                    default => '-',
                                                }),
                                        ])->columns(5)
                                ]),

                        ]),

                    Tables\Actions\EditAction::make()
                        ->label('Ubah'),

                    Tables\Actions\Action::make('batal')
                        ->label('Batal Retur')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Batal Retur?')
                        ->modalDescription('Tindakan ini akan membatalkan retur dan mengembalikan stok produk ke distribusi. Pastikan tidak ada retur yang terkait dengan distribusi ini sebelum membatalkannya.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->modalCancelActionLabel('Tidak')
                        ->action(fn($record) => $record->delete())
                        ->visible(fn($record) => $record->deleted_at === null),
                ])->color('black'),
            ])->actionsColumnLabel('Aksi');
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
