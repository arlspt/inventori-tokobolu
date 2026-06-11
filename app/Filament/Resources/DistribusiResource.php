<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistribusiResource\Pages;
use App\Models\Distribusi;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\DatePicker as FormDatePicker;

class DistribusiResource extends Resource
{
    protected static ?string $model = Distribusi::class;
    protected static ?int $navigationSort = 3; // Urutan 3 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Distribusi';
    protected static ?string $pluralModelLabel = 'Distribusi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // SECTION DATA UTAMA
                Section::make('Data Distribusi')
                    ->columns(2)
                    ->schema([

                        // KIRI
                        Grid::make(1)
                            ->schema([
                                DatePicker::make('tanggal')
                                    ->default(now())
                                    ->required(),

                                Hidden::make('user_id')
                                    ->default(fn() => Auth::id()),
                                Hidden::make('status_pembayaran')
                                    ->default('belum_bayar'),
                                Hidden::make('jumlah_awal'),

                                Select::make('reseller_id')
                                    ->placeholder('Pilih Reseller')
                                    ->preload()
                                    ->live()
                                    ->relationship('reseller', 'nama_reseller')
                                    ->searchable()
                                    ->requiredWithout('tujuan_lain')
                                    ->visible(fn($get) => blank($get('tujuan_lain')))
                                    ->createOptionForm([
                                        TextInput::make('nama_reseller')
                                            ->label('Nama Reseller')
                                            ->required(),
                                        TextInput::make('no_telp')
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
                                            ->required(),
                                    ])
                                    ->createOptionAction(function ($action) {
                                        return $action
                                            ->label('Tambah Reseller')
                                            ->modalHeading('Tambah Reseller Baru')
                                            ->modalSubmitActionLabel('Simpan')
                                            ->modalCancelActionLabel('Batal');
                                    }),

                                TextInput::make('tujuan_lain')
                                    ->label('Customer / Tujuan Lain')
                                    ->placeholder('Isi jika bukan untuk reseller')
                                    ->live()
                                    ->visible(fn($get) => blank($get('reseller_id'))),
                            ])
                            ->columnSpan(1),

                        // 🔥 KANAN (INI KUNCINYA)
                        Grid::make(1)
                            ->schema([
                                TextInput::make('nomor_invoice')
                                    ->label('Nomor Invoice')
                                    ->default(fn() => Distribusi::generateInvoice())
                                    ->readOnly()
                                    ->dehydrated(true)
                                // ->visible(fn($livewire) => !($livewire instanceof CreateRecord))
                                ,

                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->dehydrated(true)
                                    ->rows(6),
                            ])
                            ->columnSpan(1),

                    ]),

                // SECTION DETAIL (INI REPEATER)
                Section::make('Detail Varian')
                    ->schema([

                        Repeater::make('detail') // nama relasi
                            ->relationship()
                            ->columns(2)
                            ->schema([

                                Select::make('produk_id')
                                    ->label('Varian')
                                    ->relationship('produk', 'nama_produk')
                                    ->searchable()
                                    ->placeholder('Pilih Varian')
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->options(function ($get) {
                                        // ✅ ambil semua produk_id yang sudah dipilih di repeater lain
                                        $dipilih = collect($get('../../detail'))
                                            ->pluck('produk_id')
                                            ->filter()
                                            ->values()
                                            ->toArray();

                                        return \App\Models\Produk::all()
                                            ->mapWithKeys(function ($produk) use ($dipilih) {
                                                $label = $produk->nama_produk;

                                                // ✅ tandai yang sudah dipilih sebagai disabled
                                                if (in_array($produk->id, $dipilih)) {
                                                    $label = $produk->nama_produk . ' (sudah dipilih)';
                                                }

                                                return [$produk->id => $label];
                                            });
                                    })
                                    ->disableOptionWhen(function ($value, $get) {
                                        // ✅ disable opsi yang sudah dipilih di row lain
                                        $dipilih = collect($get('../../detail'))
                                            ->pluck('produk_id')
                                            ->filter()
                                            ->values()
                                            ->toArray();

                                        // cek apakah value ini sudah dipakai, tapi JANGAN disable milik row sendiri
                                        $currentProdukId = $get('produk_id');

                                        if ($value == $currentProdukId) return false; // row sendiri boleh

                                        return in_array($value, $dipilih);
                                    })
                                    ->afterStateUpdated(function ($state, $set) {
                                        $set('jumlah', 1);
                                        $produk = \App\Models\Produk::find($state);
                                        if ($produk) {
                                            $set('harga', $produk->harga);
                                            $set('subtotal', $produk->harga * 1);
                                        }
                                    }),
                                TextInput::make('harga')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated(), // tetap disimpan ke DB

                                TextInput::make('jumlah')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->helperText(function ($get) {
                                        $produk = \App\Models\Produk::find($get('produk_id'));
                                        return $produk ? "Stok tersedia: {$produk->stok}" : null;
                                    })
                                    ->afterStateUpdated(function ($state, $get, $set) {

                                        $produkId = $get('produk_id');
                                        if (!$produkId || !$state) return;

                                        $produk = \App\Models\Produk::find($produkId);
                                        if (!$produk) return;

                                        if (!$state) return;
                                        $set('jumlah_awal', $state);

                                        // 🔥 VALIDASI REALTIME
                                        if ($state > $produk->stok) {
                                            $set('jumlah', $produk->stok);

                                            Notification::make()
                                                ->title('Stok tidak cukup')
                                                ->body("Maksimal hanya {$produk->stok}")
                                                ->danger()
                                                ->send();
                                        }

                                        // 🔥 HITUNG SUBTOTAL
                                        $harga = $get('harga') ?? 0;
                                        $set('subtotal', $harga * ($get('jumlah') ?? 0));
                                    })
                                    ->rule(function ($get) {
                                        return function ($attribute, $value, $fail) use ($get) {

                                            $produk = \App\Models\Produk::find($get('produk_id'));

                                            if ($produk && $value > $produk->stok) {
                                                $fail("Stok tidak cukup. Tersedia: {$produk->stok}");
                                            }
                                        };
                                    })
                                // ->disabled(
                                //     fn($get) =>
                                //     optional(\App\Models\Produk::find($get('produk_id')))->stok == 0
                                // )
                                ,
                                TextInput::make('subtotal')
                                    ->numeric()
                                    ->prefix('Rp.')
                                    ->disabled()
                                    ->dehydrated()
                            ])
                            ->addAction(
                                fn($action) =>
                                $action
                                    ->label('Tambah Varian')
                                    ->icon('heroicon-m-plus')
                            )
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) => $query
                    ->with(['detail.produk', 'reseller', 'user'])
                    ->orderByRaw("CASE WHEN status = 'dibatalkan' THEN 1 ELSE 0 END ASC")
                    ->orderBy('created_at', 'desc')
            )
            ->recordUrl(null) // penting
            ->recordAction('view') // klik row -> modal
            ->searchPlaceholder('Cari Tujuan')
            ->columns([
                TextColumn::make('nomor_invoice')
                    ->label('No. Invoice')
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

                TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->getStateUsing(
                        fn($record) =>
                        $record->reseller
                            ? $record->reseller->nama_reseller
                            : $record->tujuan_lain
                    )
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas(
                                'reseller',
                                fn($q) =>
                                $q->where('nama_reseller', 'like', "%{$search}%")
                            )
                                ->orWhere('tujuan_lain', 'like', "%{$search}%");
                        });
                    }),

                TextColumn::make('detail_count')
                    ->counts('detail')
                    ->label('Jumlah Item')
                    ->alignCenter(),

                TextColumn::make('total')
                    ->label('Total')
                    ->alignEnd()      // ✅ isi kanan
                    ->getStateUsing(
                        fn($record) =>
                        $record->detail->sum('subtotal')
                    )
                    ->formatStateUsing(
                        fn($state) =>
                        'Rp ' . number_format($state, 0, ',', '.')
                    ),

                TextColumn::make('status_pembayaran')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'belum_bayar' => 'Belum Bayar',
                        'lunas' => 'Lunas',
                    })
                    ->color(fn($state) => match ($state) {
                        'lunas' => 'success',
                        'belum_bayar' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'dikirim' => 'Dikirim',
                        'dibatalkan' => 'Dibatalkan',
                    })
                    ->color(fn($state) => match ($state) {
                        'dikirim' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->options([
                        'dikirim' => 'Dikirim',
                        'dibatalkan' => 'Dibatalkan',
                    ]),
                Tables\Filters\SelectFilter::make('reseller_id')
                    ->label('Reseller')
                    ->relationship('reseller', 'nama_reseller')
                    ->placeholder('Semua'),
            ])
            ->headerActions([
                Action::make('rekap_bulanan')
                    ->label('Rekap Bulanan Distribusi')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('gray')
                    ->form([
                        FormSelect::make('tipe_tujuan')
                            ->label('Tipe Tujuan')
                            ->options([
                                'reseller'    => 'Reseller',
                                'tujuan_lain' => 'Customer',
                            ])
                            ->required()
                            ->placeholder('Pilih tipe tujuan')
                            ->native(false)
                            ->live(),

                        FormSelect::make('reseller_id')
                            ->label('Reseller')
                            ->options(
                                ['all' => 'Semua Reseller'] +
                                    \App\Models\Reseller::pluck('nama_reseller', 'id')->toArray()
                            )->searchable()
                            ->required()
                            ->placeholder('Pilih Reseller')
                            ->visible(fn($get) => $get('tipe_tujuan') === 'reseller')
                            ->live(),

                        FormSelect::make('bulan')
                            ->label('Bulan')
                            ->required()
                            ->placeholder('Pilih bulan')
                            ->native(false)
                            ->options(function ($get) {
                                $tipe = $get('tipe_tujuan');
                                if (!$tipe) return [];

                                if ($tipe === 'reseller') {

                                    $resellerId = $get('reseller_id');

                                    if (!$resellerId) {
                                        return [];
                                    }

                                    $query = Distribusi::where('status', '!=', 'dibatalkan');

                                    if ($resellerId !== 'all') {
                                        $query->where('reseller_id', $resellerId);
                                    }

                                    return $query
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

                                // tujuan_lain → semua bulan yang ada distribusi tujuan_lain
                                return Distribusi::whereNotNull('tujuan_lain')
                                    ->where('status', '!=', 'dibatalkan')
                                    ->selectRaw('DATE_FORMAT(tanggal, "%Y-%m") as bulan_key')
                                    ->distinct()
                                    ->orderByRaw('bulan_key DESC')
                                    ->pluck('bulan_key')
                                    ->mapWithKeys(fn($b) => [$b => Carbon::createFromFormat('Y-m', $b)->locale('id')->translatedFormat('F Y')])
                                    ->toArray();
                            })
                            ->live(),
                    ])
                    ->action(function (array $data) {
                        $url = route('invoice.rekap-bulanan', [
                            'tipe_tujuan' => $data['tipe_tujuan'],
                            'reseller_id' => $data['reseller_id'] ?? null,
                            'bulan'       => $data['bulan'],
                        ]);
                        return redirect($url);
                    })
                    ->modalHeading('Cetak Rekap Bulanan')
                    ->modalSubmitActionLabel('Cetak')
                    ->modalCancelActionLabel('Batal'),
            ])
            ->actions([
                // DROPDOWN MENU
                ActionGroup::make([
                    // DETAIL (dipanggil saat klik row & dropdown)
                    ViewAction::make('view')
                        ->label('Detail')
                        ->modalHeading('Detail Distribusi')
                        ->color('info')
                        ->modalFooterActions([
                            Action::make('retur')
                                ->label(
                                    fn($record) =>
                                    \App\Models\Retur::where('distribusi_id', $record->id)
                                        ->whereNull('deleted_at')
                                        ->exists()
                                        ? 'Ubah Retur'
                                        : 'Buat Retur'
                                )
                                ->icon('heroicon-o-arrow-uturn-left')
                                ->color('warning')
                                ->visible(fn($record) => $record->status !== 'dibatalkan')
                                ->action(function ($record) {
                                    $retur = \App\Models\Retur::where('distribusi_id', $record->id)
                                        ->whereNull('deleted_at')
                                        ->first();
                                    // kalau sudah ada -> edit
                                    if ($retur) {
                                        return redirect()->to(
                                            route('filament.admin.resources.returs.edit', [
                                                'record' => $retur->id
                                            ])
                                        );
                                    }
                                    // kalau belum ada -> create
                                    return redirect()->to(
                                        route('filament.admin.resources.returs.create', [
                                            'distribusi_id' => $record->id
                                        ])
                                    );
                                }),

                            Action::make('lunas')
                                ->label('Tandai Lunas')
                                ->icon('heroicon-o-check-circle')
                                ->color('success')
                                ->visible(
                                    fn($record) =>
                                    $record->status !== 'dibatalkan' &&
                                        $record->status_pembayaran !== 'lunas'
                                )
                                ->requiresConfirmation()
                                ->modalHeading('Tandai Lunas?')
                                ->modalDescription('Tindakan ini akan menandai distribusi sebagai lunas. Pastikan pembayaran sudah diterima sebelum melakukan tindakan ini.')
                                ->modalSubmitActionLabel('Ya, Tandai Lunas')
                                ->modalCancelActionLabel('Tidak')
                                ->action(function ($record) {

                                    $record->update([
                                        'status_pembayaran' => 'lunas'
                                    ]);

                                    Notification::make()
                                        ->title('Pembayaran ditandai lunas')
                                        ->success()
                                        ->send();
                                }),

                            Action::make('cetak_invoice')
                                ->label('Cetak Invoice')
                                ->icon('heroicon-o-printer')
                                ->color('gray')
                                ->url(fn($record) => route('invoice.cetak', $record->id))
                                ->openUrlInNewTab(),
                        ])
                        ->infolist([
                            // SECTION DATA DISTRIBUSI
                            InfoSection::make('Data Distribusi')
                                ->schema([
                                    TextEntry::make('nomor_invoice')
                                        ->label('No. Invoice'),

                                    TextEntry::make('tanggal')
                                        ->label('Tanggal')
                                        ->formatStateUsing(
                                            fn($state) =>
                                            Carbon::parse($state)
                                                ->locale('id')
                                                ->translatedFormat('l, d F Y')
                                        ),

                                    TextEntry::make('tujuan')
                                        ->label('Tujuan')
                                        ->getStateUsing(
                                            fn($record) =>
                                            $record->reseller
                                                ? $record->reseller->nama_reseller
                                                : $record->tujuan_lain
                                        ),

                                    TextEntry::make('status_pembayaran')
                                        ->label('Status Pembayaran')
                                        ->badge()
                                        ->formatStateUsing(fn($state) => match ($state) {
                                            'belum_bayar' => 'Belum Bayar',
                                            'lunas' => 'Lunas',
                                        })
                                        ->color(fn($state) => match ($state) {
                                            'lunas' => 'success',
                                            'belum_bayar' => 'warning',
                                        }),

                                    TextEntry::make('keterangan')
                                        ->label('Keterangan')
                                        ->placeholder('-'),
                                ])
                                ->columns(3),

                            // SECTION DETAIL PRODUK
                            InfoSection::make('Data Varian')
                                ->schema([
                                    \Filament\Infolists\Components\View::make('infolists.components.distribusi-detail-table')
                                        ->viewData(fn($record) => ['detail' => $record->detail])
                                ]),
                        ]),
                    Tables\Actions\EditAction::make()
                        ->label('Ubah')
                        ->visible(fn($record) => $record->status !== 'dibatalkan'),

                    Action::make('retur')
                        ->label(
                            fn($record) =>
                            \App\Models\Retur::where('distribusi_id', $record->id)
                                ->whereNull('deleted_at')
                                ->exists()
                                ? 'Ubah Retur'
                                : 'Buat Retur'
                        )->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->visible(fn($record) => $record->status !== 'dibatalkan')
                        ->url(function ($record) {

                            $retur = \App\Models\Retur::where('distribusi_id', $record->id)
                                ->whereNull('deleted_at')
                                ->first();

                            // kalau sudah ada -> edit
                            if ($retur) {
                                return route('filament.admin.resources.returs.edit', [
                                    'record' => $retur->id
                                ]);
                            }

                            // kalau belum ada -> create
                            return route('filament.admin.resources.returs.create', [
                                'distribusi_id' => $record->id
                            ]);
                        }),
                    Action::make('lunas')
                        ->label('Tandai Lunas')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(
                            fn($record) =>
                            $record->status !== 'dibatalkan' &&
                                $record->status_pembayaran !== 'lunas'
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Lunas?')
                        ->modalDescription('Tindakan ini akan menandai distribusi sebagai lunas. Pastikan pembayaran sudah diterima sebelum melakukan tindakan ini.')
                        ->modalSubmitActionLabel('Ya, Tandai Lunas')
                        ->modalCancelActionLabel('Tidak')
                        ->action(function ($record) {

                            $record->update([
                                'status_pembayaran' => 'lunas'
                            ]);

                            Notification::make()
                                ->title('Pembayaran ditandai lunas')
                                ->success()
                                ->send();
                        }),

                    Action::make('batalkan')
                        ->label('Batalkan')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->visible(fn($record) => $record->status !== 'dibatalkan')
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan?')
                        ->modalDescription('Tindakan ini akan membatalkan distribusi dan mengembalikan stok produk. Pastikan tidak ada retur yang terkait dengan distribusi ini sebelum membatalkannya.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->modalCancelActionLabel('Tidak')
                        ->action(function ($record) {
                            $adaRetur = \App\Models\Retur::where('distribusi_id', $record->id)
                                ->whereNull('deleted_at')
                                ->exists();
                            if ($adaRetur) {
                                Notification::make()
                                    ->title('Tidak bisa dibatalkan')
                                    ->body('Distribusi sudah memiliki retur.')
                                    ->danger()
                                    ->send();
                                throw ValidationException::withMessages([
                                    'batalkan' => 'Distribusi sudah memiliki retur.'
                                ]);
                            }
                            $record->batalkan();
                            Notification::make()
                                ->title('Distribusi berhasil dibatalkan')
                                ->success()
                                ->send();
                        }),
                ])->color('black'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDistribusis::route('/'),
            'create' => Pages\CreateDistribusi::route('/create'),
            'edit' => Pages\EditDistribusi::route('/{record}/edit'),
        ];
    }
}
