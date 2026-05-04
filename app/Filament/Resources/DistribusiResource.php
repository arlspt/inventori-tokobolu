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

class DistribusiResource extends Resource
{
    protected static ?string $model = Distribusi::class;
    protected static ?int $navigationSort = 3; // Urutan 3 menu di sidebar
    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-right';
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
                                    ->requiredWithout('tujuan_lain'),

                                TextInput::make('tujuan_lain')
                                    ->label('Tujuan Lain')
                                    ->placeholder('Isi jika bukan untuk reseller')
                                    ->visible(fn($get) => !$get('reseller_id')),
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
                                    ->minValue(1)
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
                    ),

                TextColumn::make('detail_count')
                    ->counts('detail')
                    ->label('Jumlah Item'),

                TextColumn::make('total')
                    ->label('Total')
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
                                ->action(function ($record) {

                                    $record->update([
                                        'status_pembayaran' => 'lunas'
                                    ]);

                                    Notification::make()
                                        ->title('Pembayaran ditandai lunas')
                                        ->success()
                                        ->send();
                                })
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
                            InfoSection::make('Data Varian')->label('Data Varian')
                                ->schema([
                                    RepeatableEntry::make('detail')->label('Detail Varian')
                                        ->schema([
                                            TextEntry::make('produk.nama_produk')
                                                ->label('Varian')
                                                ->columnSpan(2),

                                            TextEntry::make('jumlah')
                                                ->label('Jumlah'),

                                            TextEntry::make('harga')
                                                ->label('Harga')
                                                ->formatStateUsing(
                                                    fn($state) =>
                                                    'Rp ' . number_format($state, 0, ',', '.')
                                                ),

                                            TextEntry::make('subtotal')
                                                ->label('Subtotal')
                                                ->formatStateUsing(
                                                    fn($state) =>
                                                    'Rp ' . number_format($state, 0, ',', '.')
                                                ),
                                        ])
                                        ->columns(5),
                                ]),

                            // SECTION TOTAL
                            InfoSection::make('Total')
                                ->schema([
                                    TextEntry::make('total')
                                        ->label('Total Keseluruhan')
                                        ->getStateUsing(
                                            fn($record) =>
                                            'Rp ' . number_format(
                                                $record->detail->sum('subtotal'),
                                                0,
                                                ',',
                                                '.'
                                            )
                                        )
                                        ->weight('bold'),
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
